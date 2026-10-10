@props(['name', 'label'])
<tr class="border-t border-slate-200">
    <td class="py-1.5 pe-3 text-sm text-slate-700">{{ $label }}</td>
    <td class="px-3 text-center"><input type="radio" name="{{ $name }}" value="yes" aria-label="Yes: {{ $label }}" class="text-amber-600 focus:ring-amber-500"></td>
    <td class="px-3 text-center"><input type="radio" name="{{ $name }}" value="no" aria-label="No: {{ $label }}" class="text-amber-600 focus:ring-amber-500"></td>
</tr>
