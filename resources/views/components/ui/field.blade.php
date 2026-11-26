{{--
    A form field: label, control (the slot), hint and validation error.
    <x-ui.field label="Nama" for="nama" name="nama" form="create" required>
        <x-ui.input id="nama" name="nama" />
    </x-ui.field>

    `name` is the validation key. `form` says which form on the page this field
    belongs to: pages with several forms post a hidden `_form` input, and the
    error is shown only under the form that was submitted.
--}}
@props(['label' => null, 'for' => null, 'name' => null, 'form' => null, 'hint' => null, 'required' => false])

@php
    $error = null;
    if ($name && $errors->has($name) && ($form === null || old('_form') === $form || str_starts_with((string) old('_form'), $form . ':'))) {
        $error = $errors->first($name);
    }
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-2']) }}>
    @if ($label)
        <x-ui.label :for="$for" :required="$required" @class(['text-destructive' => $error])>{{ $label }}</x-ui.label>
    @endif
    {{ $slot }}
    @if ($error)
        <p class="text-destructive text-sm">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-muted-foreground text-sm">{{ $hint }}</p>
    @endif
</div>
