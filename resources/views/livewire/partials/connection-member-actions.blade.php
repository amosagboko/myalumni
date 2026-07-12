@php
    $mode = $mode ?? 'none';
    $userId = $userId ?? null;
    $style = $style ?? 'default';
@endphp

@if($mode === 'accepted')
    <button type="button"
            wire:click="unfriend({{ $userId }})"
            wire:loading.attr="disabled"
            wire:target="unfriend({{ $userId }})"
            class="mt-0 btn pt-2 pb-2 ps-3 pe-3 lh-24 ls-3 d-inline-block rounded-xl bg-danger font-xsssss fw-700 ls-lg text-white border-0 {{ $style === 'hero' ? 'ps-4 pe-4 text-uppercase' : '' }}">
        Connected
    </button>
@elseif($mode === 'pending')
    <span class="mt-0 btn pt-2 pb-2 ps-3 pe-3 lh-24 ls-3 d-inline-block rounded-xl bg-warning font-xsssss fw-700 ls-lg text-dark {{ $style === 'hero' ? 'ps-4 pe-4 text-uppercase' : '' }}">Pending</span>
@elseif($mode === 'received')
    <div class="d-flex flex-column gap-2 {{ $style === 'hero' ? 'align-items-center' : '' }}">
        <button type="button"
                wire:click="acceptRequest({{ $userId }})"
                wire:loading.attr="disabled"
                wire:target="acceptRequest({{ $userId }})"
                class="btn pt-2 pb-2 ps-3 pe-3 lh-24 rounded-xl bg-success font-xsssss fw-700 text-white border-0 {{ $style === 'hero' ? 'ps-4 pe-4 text-uppercase' : 'w-100' }}">
            Confirm
        </button>
        <button type="button"
                wire:click="rejectRequest({{ $userId }})"
                wire:loading.attr="disabled"
                wire:target="rejectRequest({{ $userId }})"
                class="btn pt-2 pb-2 ps-3 pe-3 lh-24 rounded-xl bg-grey text-grey-800 font-xsssss fw-700 border-0 {{ $style === 'hero' ? 'ps-4 pe-4 text-uppercase' : 'w-100' }}">
            Delete
        </button>
    </div>
@else
    <button type="button"
            wire:click="sendRequest({{ $userId }})"
            wire:loading.attr="disabled"
            wire:target="sendRequest({{ $userId }})"
            class="mt-0 btn pt-2 pb-2 ps-3 pe-3 lh-24 ls-3 d-inline-block rounded-xl bg-success font-xsssss fw-700 ls-lg text-white border-0 {{ $style === 'hero' ? 'ps-4 pe-4 text-uppercase' : '' }}">
        <span wire:loading.remove wire:target="sendRequest({{ $userId }})">Connect</span>
        <span wire:loading wire:target="sendRequest({{ $userId }})">Sending...</span>
    </button>
@endif
