<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">Audit Trail</h2>
        <p class="mt-1 text-sm text-slate-600">User actions, data changes and security events</p>
    </x-slot>

    @php $types = ['All', 'User action', 'Data change', 'Security event']; @endphp

    <div class="py-6" x-data="{ f: 'All', rows: @js(collect($events)->pluck('type')->values()) }">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-2">
                @foreach ($types as $t)
                    <button type="button" @click="f = '{{ $t }}'" :aria-pressed="f === '{{ $t }}'" class="rounded-full border px-4 py-1.5 text-sm" :class="f === '{{ $t }}' ? 'border-[#1c3d34] bg-[#1c3d34] text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'">{{ $t }}</button>
                @endforeach
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3 font-medium">When</th><th class="px-4 py-3 font-medium">User</th><th class="px-4 py-3 font-medium">Type</th><th class="px-4 py-3 font-medium">Event</th><th class="px-4 py-3 font-medium">Details</th><th class="px-4 py-3 font-medium">Device</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($events as $e)
                            <tr x-show="f === 'All' || f === rows[{{ $loop->index }}]">
                                <td class="whitespace-nowrap px-4 py-2">{{ \Carbon\Carbon::parse($e['at'])->format('M d, g:i A') }}</td>
                                <td class="px-4 py-2">{{ $e['user'] }}</td>
                                <td class="px-4 py-2">{{ $e['type'] }}</td>
                                <td class="px-4 py-2 font-medium">{{ $e['event'] }}</td>
                                <td class="px-4 py-2">{{ $e['details'] }}</td>
                                <td class="px-4 py-2">{{ $e['source'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
