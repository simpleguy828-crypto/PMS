<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Computer Preventive Maintenance Summary</title>
    <style>
        @page { margin: 48px 54px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; line-height: 1.55; }
        h1 { margin: 0 0 18px; text-align: center; font-size: 14px; }
        h2 { margin: 14px 0 7px; font-size: 11px; }
        p { margin: 5px 0 10px; }
        .period { margin-bottom: 14px; font-weight: bold; }
        .overview { margin-left: 16px; }
        .tasks { margin: 5px 0 16px 28px; padding-left: 14px; }
        .office { margin: 0 0 14px 28px; page-break-inside: avoid; }
        .office-title { margin: 0 0 4px; font-weight: bold; }
        .details { margin: 0 0 0 20px; padding-left: 14px; }
        .details li { margin: 2px 0; }
        .detail-label { margin: 5px 0 0 20px; }
    </style>
</head>
<body>
    <h1>COMPUTER PREVENTIVE MAINTENANCE SUMMARY</h1>
    <p class="period">Month/Year: {{ $periodLabel }}</p>

    <h2>A. Overview</h2>
    <p class="overview">
        The Management Information System Office conducted a preventive maintenance procedure on computers in {{ $officeLabel }} throughout {{ strtolower($periodLabel) }} to ensure systems operate at their best. A total of {{ $totalComputersChecked }} computers were checked and {{ $totalIssuesFound }} issues were found. Activities encompassed checking memory and storage capacity, inspecting hardware connections, running virus scans, and enhancing overall system performance.
    </p>

    <p><strong>Office-Wise Maintenance Summary</strong></p>
    <p><strong>Tasks Performed:</strong></p>
    <ul class="tasks">
        @foreach($tasksPerformed as $task)
            <li>{{ $task }}</li>
        @endforeach
    </ul>

    @forelse($officeSummaries as $index => $office)
        <div class="office">
            <p class="office-title">{{ $index + 1 }}. {{ $office['name'] }}</p>
            <ul class="details">
                <li><strong>Total Computers Checked:</strong> {{ $office['computers_checked'] }}</li>
            </ul>
            <p class="detail-label"><strong>Findings:</strong></p>
            <ul class="details">
                @forelse($office['findings'] as $finding)
                    <li>{{ $finding['name'] }} - {{ $finding['computers'] }} {{ $finding['computers'] === 1 ? 'computer' : 'computers' }}</li>
                @empty
                    <li>NONE</li>
                @endforelse
            </ul>
            <p class="detail-label"><strong>Recommendation:</strong></p>
            <ul class="details">
                @forelse($office['recommendations'] as $recommendation)
                    <li>{{ $recommendation['text'] }} - {{ $recommendation['computers'] }} {{ $recommendation['computers'] === 1 ? 'computer' : 'computers' }}</li>
                @empty
                    <li>NONE</li>
                @endforelse
            </ul>
        </div>
    @empty
        <p class="office">No preventive maintenance records were found for this period and office filter.</p>
    @endforelse
</body>
</html>