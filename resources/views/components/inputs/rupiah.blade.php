@props(['model' => null, 'wireless' => false])

<div class="input-group rupiah-input-group">
    <span class="input-group-text">Rp</span>
    <input
        type="text"
        {{ $attributes->merge(['class' => 'form-control rupiah-input']) }}
        @unless($wireless || blank($model))
            wire:model.live="{{ $model }}"
        @endunless
        inputmode="numeric"
        autocomplete="off"
    >
</div>
