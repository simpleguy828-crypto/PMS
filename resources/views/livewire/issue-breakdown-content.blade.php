@forelse($issueBreakdown as $section => $sectionIssues)
    <section class="mb-5 last:mb-0">
        <h4 class="text-sm font-semibold text-amber-950">{{ $section }}</h4>
        <div class="mt-2 space-y-3 pl-3">
            @foreach($sectionIssues['findings'] as $finding)
                <div>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $finding['name'] }} - {{ $finding['total'] }} {{ $finding['total'] === 1 ? 'computer' : 'computers' }}
                    </p>
                    <ul class="mt-1 space-y-1 text-sm text-gray-700">
                        @foreach($finding['offices'] as $officeIssue)
                            <li class="flex justify-between gap-3">
                                <span>{{ $officeIssue['name'] }}</span>
                                <span class="font-medium">{{ $officeIssue['count'] }} {{ $officeIssue['count'] === 1 ? 'computer' : 'computers' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
@empty
    <p class="text-sm text-gray-600">No defective issues found for these filters.</p>
@endforelse