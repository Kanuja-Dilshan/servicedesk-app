<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketCategory, TicketPriority, TicketStatus, User};
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $r)
    {
        [$from, $to] = $this->range($r);
        $tickets = $this->tickets($from, $to);

        $active = $tickets->filter(fn ($t) => $t->status_id < Ticket::RESOLVED);
        $resolved = $tickets->filter(fn ($t) => $t->resolved_at);
        $withSla = $resolved->filter(fn ($t) => $t->due_at);
        $slaMet = $withSla->filter(fn ($t) => $this->slaMet($t));

        $kpi = [
            'total' => $tickets->count(),
            'resolved' => $resolved->count(),
            'overdue' => $active->filter(fn ($t) => $t->due_at && $t->due_at->isPast())->count(),
            'avg_hours' => $resolved->count() ? round($resolved->avg(fn ($t) => $this->hours($t)), 1) : null,
            'sla' => $withSla->count() ? (int) round($slaMet->count() / $withSla->count() * 100) : null,
        ];

        $count = fn ($model, $col) => $model::all()->mapWithKeys(
            fn ($m) => [$m->name => $tickets->where($col, $m->id)->count()]
        );

        // trend: per day, or per week when the period is long
        $byWeek = $from->diffInDays($to) > 62;
        $bucket = fn (Carbon $d) => ($byWeek ? $d->copy()->startOfWeek() : $d->copy())->toDateString();
        $trend = [];
        for ($c = $byWeek ? $from->copy()->startOfWeek() : $from->copy(); $c->lte($to); $c->addDays($byWeek ? 7 : 1)) {
            $trend[$c->toDateString()] = 0;
        }
        foreach ($tickets as $t) {
            $k = $bucket($t->created_at);
            if (isset($trend[$k])) {
                $trend[$k]++;
            }
        }

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'kpi' => $kpi,
            'byStatus' => $count(TicketStatus::class, 'status_id'),
            'byPriority' => $count(TicketPriority::class, 'priority_id'),
            'byCategory' => $count(TicketCategory::class, 'category_id'),
            'techs' => $this->techStats($tickets),
            'trend' => $trend,
            'byWeek' => $byWeek,
        ]);
    }

    public function export(Request $r)
    {
        $r->validate(['type' => 'nullable|in:tickets,technicians']);
        [$from, $to] = $this->range($r);
        $tickets = $this->tickets($from, $to);
        $type = $r->query('type', 'tickets');
        $file = "servicedesk-{$type}-{$from->toDateString()}-to-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($tickets, $type) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly

            if ($type === 'technicians') {
                fputcsv($out, ['Technician', 'Assigned', 'Active now', 'Resolved', 'Avg resolution (hours)', 'SLA met (%)']);
                foreach ($this->techStats($tickets) as $t) {
                    fputcsv($out, [$this->safe($t['name']), $t['assigned'], $t['active'], $t['resolved'], $t['avg_hours'], $t['sla']]);
                }
            } else {
                fputcsv($out, ['Ticket ID', 'Title', 'Category', 'Priority', 'Status', 'Requester', 'Department', 'Assigned to', 'Created', 'Due', 'Resolved', 'Resolution (hours)', 'SLA met']);
                foreach ($tickets as $t) {
                    fputcsv($out, [
                        $t->id,
                        $this->safe($t->title),
                        $this->safe($t->category?->name),
                        $t->priority?->name,
                        $t->status?->name,
                        $this->safe($t->creator?->name),
                        $this->safe($t->creator?->department?->name),
                        $this->safe($t->technician?->name ?? 'Unassigned'),
                        $t->created_at?->format('Y-m-d H:i'),
                        $t->due_at?->format('Y-m-d H:i'),
                        $t->resolved_at ? Carbon::parse($t->resolved_at)->format('Y-m-d H:i') : '',
                        $this->hours($t),
                        $t->resolved_at && $t->due_at ? ($this->slaMet($t) ? 'Yes' : 'No') : '',
                    ]);
                }
            }
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $r): array
    {
        $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $to = $r->filled('to') ? Carbon::parse($r->query('to'))->endOfDay() : now()->endOfDay();
        $from = $r->filled('from') ? Carbon::parse($r->query('from'))->startOfDay() : now()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function tickets(Carbon $from, Carbon $to)
    {
        return Ticket::with(['status', 'priority', 'category', 'creator.department', 'technician'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('id')
            ->get();
    }

    private function techStats($tickets)
    {
        return User::where('role', 'support')->orderBy('name')->get()->map(function ($u) use ($tickets) {
            $mine = $tickets->where('assigned_to', $u->id);
            $resolved = $mine->filter(fn ($t) => $t->resolved_at);
            $withSla = $resolved->filter(fn ($t) => $t->due_at);

            return [
                'name' => $u->name,
                'assigned' => $mine->count(),
                'active' => $mine->filter(fn ($t) => $t->status_id < Ticket::RESOLVED)->count(),
                'resolved' => $resolved->count(),
                'avg_hours' => $resolved->count() ? round($resolved->avg(fn ($t) => $this->hours($t)), 1) : null,
                'sla' => $withSla->count() ? (int) round($withSla->filter(fn ($t) => $this->slaMet($t))->count() / $withSla->count() * 100) : null,
            ];
        });
    }

    private function hours(Ticket $t): ?float
    {
        return $t->resolved_at ? round($t->created_at->diffInMinutes(Carbon::parse($t->resolved_at)) / 60, 1) : null;
    }

    private function slaMet(Ticket $t): bool
    {
        return Carbon::parse($t->resolved_at)->lte($t->due_at);
    }

    /** Stop spreadsheet apps from running text such as "=SUM(...)" as a formula. */
    private function safe(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) ? "'" . $value : $value;
    }
}