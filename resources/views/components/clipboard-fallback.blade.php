{{-- Browsers expose navigator.clipboard on secure origins only, so a self-hosted
     install served over plain http gets this stand-in behind every copy button. --}}
<script data-clipboard-fallback>
    (() => {
        if (navigator.clipboard) {
            return
        }

        Object.defineProperty(navigator, 'clipboard', {
            configurable: true,
            value: {
                writeText(text) {
                    const focused = document.activeElement
                    const field = document.createElement('textarea')

                    field.value = text
                    field.readOnly = true
                    field.style.position = 'fixed'
                    field.style.opacity = '0'

                    // Beside the focused element, never on body: a modal's focus trap
                    // pulls focus back from body and the selection is lost.
                    const host = focused && focused !== document.body ? focused.parentElement : document.body

                    host.appendChild(field)
                    field.select()

                    const copied = document.execCommand('copy')

                    field.remove()
                    focused?.focus({ preventScroll: true })

                    return copied
                        ? Promise.resolve()
                        : Promise.reject(new DOMException('The copy command was blocked.', 'NotAllowedError'))
                },
            },
        })
    })()
</script>
