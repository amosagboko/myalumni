@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: false,
        focusables() {
            return [];
        },
        firstFocusable() { return null; },
        lastFocusable() { return null; },
        nextFocusable() { return null; },
        prevFocusable() { return null; },
        nextFocusableIndex() { return 0; },
        prevFocusableIndex() { return 0; },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: none;"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>

<script>
    // Prevent modal from being initialized
    document.addEventListener('DOMContentLoaded', function() {
        // Remove all data-modal attributes
        document.querySelectorAll('[data-modal]').forEach(function(el) {
            el.removeAttribute('data-modal');
        });

        // Remove all data-toggle attributes
        document.querySelectorAll('[data-toggle]').forEach(function(el) {
            el.removeAttribute('data-toggle');
        });

        // Remove all data-target attributes
        document.querySelectorAll('[data-target]').forEach(function(el) {
            el.removeAttribute('data-target');
        });

        // Prevent modal from being shown
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-toggle="modal"]') || e.target.closest('[data-target^="#Modal"]')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        // Override Bootstrap modal
        if (typeof bootstrap !== 'undefined') {
            const Modal = bootstrap.Modal;
            const originalModal = Modal.prototype.show;
            Modal.prototype.show = function() {
                // Do nothing
            };
        }

        // Override Alpine.js modal
        if (typeof Alpine !== 'undefined') {
            Alpine.data('modal', function() {
                return {
                    show: false,
                    init() {
                        // Do nothing
                    }
                };
            });
        }
    });
</script>
