<x-filament-panels::page>
    <form wire:submit="fetch">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-arrow-path">
                Fetch from TripJack
            </x-filament::button>
        </div>
    </form>

    @if($searched)
        <div class="mt-8">
            @if(empty($results))
                <p class="text-sm text-gray-500 dark:text-gray-400">No bookings found in this date range.</p>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">TripJack Booking ID</th>
                                <th class="px-4 py-2 text-left font-semibold">TripJack Status</th>
                                <th class="px-4 py-2 text-right font-semibold">TripJack Price</th>
                                <th class="px-4 py-2 text-left font-semibold">Local Reference</th>
                                <th class="px-4 py-2 text-left font-semibold">Local Status</th>
                                <th class="px-4 py-2 text-right font-semibold">Local Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $row)
                                <tr @class([
                                    'border-t border-gray-100 dark:border-white/5',
                                    'bg-danger-50 dark:bg-danger-500/10' => $row['mismatch'],
                                ])>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $row['bookingId'] }}</td>
                                    <td class="px-4 py-2">{{ $row['tjStatus'] }}</td>
                                    <td class="px-4 py-2 text-right">{{ $row['tjTotalPrice'] !== null ? number_format($row['tjTotalPrice'], 2) : '—' }}</td>
                                    <td class="px-4 py-2">
                                        @if($row['localReference'])
                                            <a href="{{ route('filament.admin.resources.bookings.index', ['tableSearch' => $row['localReference']]) }}" class="text-primary-600 hover:underline">
                                                {{ $row['localReference'] }}
                                            </a>
                                        @else
                                            <span class="text-danger-600 font-medium">No local record</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $row['localStatus'] ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">{{ $row['localTotalPrice'] !== null ? number_format($row['localTotalPrice'], 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Rows highlighted in red have no matching local booking, or a local status that doesn't match what TripJack reports.</p>
            @endif
        </div>
    @endif
</x-filament-panels::page>
