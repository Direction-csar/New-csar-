@php
    $name = $name ?? '';
    $label = $label ?? '';
    $options = $options ?? [];
    $required = $required ?? true;
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold">{{ $label }}</label>
    <div class="d-flex flex-column gap-2">
        @foreach($options as $val => $lbl)
            <div class="form-check">
                <input class="form-check-input" type="radio"
                       name="{{ $name }}"
                       id="{{ $name }}_{{ $val }}"
                       value="{{ $val }}"
                       @if(old($name) === $val) checked @endif
                       @if($required) required @endif>
                <label class="form-check-label" for="{{ $name }}_{{ $val }}">{{ $lbl }}</label>
            </div>
        @endforeach
    </div>
</div>
