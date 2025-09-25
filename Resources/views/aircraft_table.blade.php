
@foreach($aircraft as $ac)
    <option value="{{ $ac->id }}">{{ $ac->name }} - {{ $ac->registration }}</option>
@endforeach
