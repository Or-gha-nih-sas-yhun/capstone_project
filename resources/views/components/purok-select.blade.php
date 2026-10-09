{{--
  Purok / sitio picker.

  Falls back to a free-text input when the barangay has not defined any
  puroks yet, so the form still works on a fresh install. A resident whose
  stored purok is no longer on the active list keeps it as a selectable
  option rather than silently losing the value.

  @param string $name   Field name (default "purok")
  @param string $value  Currently stored value
--}}
@php
  $name = $name ?? 'purok';
  $value = (string) ($value ?? '');
  $purokOptions = \App\Models\Purok::options();
@endphp

@if (count($purokOptions))
  <select name="{{ $name }}" class="form-select">
    <option value="">— Select Purok —</option>
    @foreach ($purokOptions as $opt)
      <option value="{{ $opt }}" @selected($value === $opt)>{{ $opt }}</option>
    @endforeach
    @if ($value !== '' && !in_array($value, $purokOptions, true))
      <option value="{{ $value }}" selected>{{ $value }} (not in current list)</option>
    @endif
  </select>
@else
  <input type="text" name="{{ $name }}" class="form-control" value="{{ $value }}" placeholder="e.g. Purok 1">
  <small class="form-text">Define your barangay's puroks under Administration → Puroks to turn this into a
    dropdown.</small>
@endif
