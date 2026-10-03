<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PM Record {{ $record->id }}</title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 30px 35px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: 1px solid #000;
            vertical-align: middle;
            padding: 12px 10px;
        }

        .logo-cell {
            width: 90px;
            text-align: center;
        }

        .logo-cell img {
            width: 65px;
            height: 65px;
        }

        .org-name {
            font-weight: bold;
            font-size: 13px;
            text-align: center;
        }

        .org-address {
            font-size: 10px;
            text-align: center;
        }

        .form-title {
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            padding-top: 8px;
            margin-bottom: 25px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 10px 8px;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            width: 12%;
        }

        .info-value {
            width: 38%;
        }

        table.checklist {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table.checklist th,
        table.checklist td {
            border: 1px solid #000;
            padding: 7px 6px;
            font-size: 11px;
            text-align: left;
            vertical-align: top;
        }

        table.checklist th {
            text-align: center;
            font-weight: bold;
        }

        .col-check {
            width: 4%;
        }

        .col-task {
            width: 34%;
        }

        .col-status {
            width: 12%;
            text-align: center;
        }

        .col-date {
            width: 16%;
            text-align: center;
        }

        .col-remarks {
            width: 34%;
        }

        .category-row td {
            font-weight: bold;
            padding: 7px 6px;
        }

        .note-box {
            border: 1px solid #000;
            border-top: none;
            padding: 14px 10px;
            font-size: 11px;
            font: Arial
        }

        .note-title {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
            font-size: 11px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .footer-table td {
            border: 1px solid #000;
            border-top: none;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }

        .footer-table .name-row td {
            border-top: none;
            font-weight: normal;
            padding-top: 11px;
        }

        .footer-table .signature-col {
            width: 47%;
            font-weight: bold;
        }

        .footer-table .divider-col {
            width: 50%;
            padding: 0;
        }

        .footer-table .date-col {
            width: 50%;
            font-weight: bold;
        }

        .prepared-by {
            width: 47%;
            margin: 8px 0 0;
            font-size: 11px;
            line-height: 1.2;
        }

        .prepared-by-label {
            font-weight: bold;
        }

        .prepared-by-gap {
            height: 5px;
        }

        .prepared-by-line {
            display: block;
            width: 220px;
            height: 12px;
            border-bottom: 1px solid #000;
        }

        .prepared-by-position {
            margin-top: 1px;
        }

        .doc-code {
            bottom: 20px;
            left: 35px;
            right: 35px;
            text-align: right;
            font-size: 11px;
        }
    </style>
</head>

<body>

    @php
        $logoPath = public_path('images/gvcf-logo.jpg');
        $logoData = base64_encode(file_get_contents($logoPath));
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="2">
                <img src="data:image/jpeg;base64,{{ $logoData }}" alt="Green Valley College Foundation logo">
            </td>
            <td>
                <div class="org-name">GREEN VALLEY COLLEGE FOUNDATION, INC.</div>
                <div class="org-address">Km.2, General Santos Drive, Koronadal City</div>
            </td>
        </tr>
        <tr>
            <td class="form-title">PREVENTIVE MAINTENANCE FORM</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label">Name:</td>
            <td class="info-value">{{ $record->requested_by_name }}</td>
            <td class="info-label">Position:</td>
            <td class="info-value">{{ $record->position }}</td>
        </tr>
        <tr>
            <td class="info-label">Department:</td>
            <td class="info-value">{{ $record->office->name }}</td>
            <td class="info-label">Date Started:</td>
            <td class="info-value">{{ optional($record->date_started)->format('m/d/Y') ?? $record->date_started }}</td>
        </tr>
    </table>

    <table class="checklist">
        <thead>
            <tr>
                <th class="col-task" colspan="2">Task</th>
                <th class="col-status">Status</th>
                <th class="col-date">Date Completed<br>(mm/dd/yyyy)</th>
                <th class="col-remarks">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grouped = $record->recordItems->groupBy(function ($item) {
                    return $item->checklistItem->section ?? null;
                });

                $statusLabels = [
                    'good' => 'Good',
                    'defective' => 'Defective',
                    'done' => 'Done',
                    'na' => 'N/A',
                    'needs_attention' => 'Needs Attention',
                ];
            @endphp

            @if ($grouped->keys()->filter()->isNotEmpty())
                @foreach ($grouped as $section => $items)
                    @if ($section)
                        <tr class="category-row">
                            <td colspan="5">{{ $section }}:</td>
                        </tr>
                    @endif
                    @foreach ($items as $item)
                        <tr>
                            <td class="col-check"></td>
                            <td>
                                {{ $item->checklistItem->task_name }}
                            </td>
                            <td class="col-status">
                                {{ $statusLabels[$item->status] ?? ($item->status ? ucfirst($item->status) : '') }}</td>
                            <td class="col-date">
                                {{ $item->date_completed ? $item->date_completed->format('m/d/Y') : '' }}</td>
                            <td>{{ $item->remarks ? $item->remarks . ($item->recommendation ? ' - ' . $item->recommendation : '') : $item->recommendation }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                @foreach ($record->recordItems as $item)
                    <tr>
                        <td class="col-check"></td>
                        <td>{{ $item->checklistItem->task_name }}</td>
                        <td class="col-status">
                            {{ $statusLabels[$item->status] ?? ($item->status ? ucfirst($item->status) : '') }}</td>
                        <td class="col-date">{{ $item->date_completed ? $item->date_completed->format('m/d/Y') : '' }}
                        </td>
                        <td>{{ $item->remarks ? $item->remarks . ($item->recommendation ? ' - ' . $item->recommendation : '') : $item->recommendation }}
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="note-box">
        <span class="note-title">NOTE:</span>
        It is your responsibility to back-up the data, information or other files stored on your computer disk and/or
        drives.
        And the MIS shall not be responsible under any circumstance for any loss or corruption of data and/or software
        or hardware or any other part as well as CD's/DVDs, and other equipment's.
    </div>

    @php
        $latestDateCompleted = $record->recordItems->pluck('date_completed')->filter()->sort()->last();
    @endphp

    <table class="footer-table">
        <tr class="name-row">
            <td class="signature-col">{{ $record->requested_by_name }}</td>
            <td class="divider-col"></td>
            <td class="date-col">{{ $latestDateCompleted ? $latestDateCompleted->format('m/d/Y') : '' }}</td>
        </tr>
        <tr>
            <td class="signature-col">SIGNATURE</td>
            <td class="divider-col"></td>
            <td class="date-col">
                DATE
            </td>
        </tr>
    </table>

    <div class="prepared-by">
        <br>
        <br>
        <div class="prepared-by-label">Prepared by:</div>
        <br>
        <div class="prepared-by-gap"></div>
        @if ($preparedBy)
            <div>{{ $preparedBy }}</div>
        @else
            <div class="prepared-by-line">&nbsp;</div>
        @endif
        <div class="prepared-by-position">{{ $preparedByPosition }}</div>
    </div>
    <br>
    <div class="doc-code">FM-MIS-008-02 Dated 11 June 2026</div>

</body>

</html>
