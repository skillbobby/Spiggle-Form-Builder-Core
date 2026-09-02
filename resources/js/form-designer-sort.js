import Sortable from 'sortablejs'

const zoneRegistry = new WeakMap()

function clearStuckSortableState() {
    document.body.classList.remove('sorting')
    document.body.style.removeProperty('user-select')
    document.querySelectorAll('.sortable-ghost, .sortable-chosen, .sortable-drag, .is-drop-target').forEach((node) => {
        node.classList.remove('sortable-ghost', 'sortable-chosen', 'sortable-drag', 'is-drop-target')
    })
    document.querySelectorAll('.sortable-fallback').forEach((node) => node.remove())
}

function forceEndActiveSort() {
    try {
        document.dispatchEvent(new MouseEvent('mouseup', { bubbles: true, cancelable: true, view: window }))
    } catch (_) {
        // Ignore synthetic event failures in strict environments.
    }

    clearStuckSortableState()
}

function registerMorphGuards() {
    if (window.__spiggleFdMorphGuards) {
        return
    }

    window.__spiggleFdMorphGuards = true

    document.addEventListener('livewire:init', () => {
        window.Livewire.hook('morph.updating', ({ el, skip }) => {
            if (! document.body.classList.contains('sorting')) {
                return
            }

            if (el.closest?.('.fd-root')) {
                skip()
            }
        })
    })
}

function zoneOptions(el, commit) {
    const zone = el.dataset.fdZone || ''
    const isSection = zone.startsWith('section_')

    return {
        animation: 120,
        handle: '.fd-drag-handle',
        // Direct children only — avoids nested section grids being claimed by the form zone.
        draggable: '> .fd-canvas-item',
        group: el.dataset.fdSortGroup || 'fd-form',
        swapThreshold: 0.65,
        emptyInsertThreshold: isSection ? 36 : 24,
        forceFallback: true,
        fallbackOnBody: true,
        fallbackTolerance: 3,
        scroll: true,
        forceAutoScrollFallback: true,
        scrollSensitivity: 50,
        preventOnFilter: false,
        filter: 'input, textarea, select, button, .fd-field-preview-control',
        onStart() {
            document.body.classList.add('sorting')
        },
        onEnd(evt) {
            document.body.classList.remove('sorting')

            const item = evt.item
            const target = evt.to
            if (! item || ! target) {
                return
            }

            const itemId = item.dataset.itemId
            const targetZone = target.dataset.fdZone
            const position = evt.newIndex

            if (! itemId || ! targetZone || position === null || position === undefined || position < 0) {
                return
            }

            commit(itemId, targetZone, position)
        },
    }
}

function mountFdZone(el, commit) {
    if (! el) {
        return null
    }

    const existing = Sortable.get(el)
    if (existing && zoneRegistry.get(el) === existing) {
        return existing
    }

    if (zoneRegistry.has(el)) {
        unmountFdZone(el)
    }

    const instance = Sortable.create(el, zoneOptions(el, commit))
    zoneRegistry.set(el, instance)

    if (window.__spiggleFdDebug) {
        console.info('[form-designer] mounted sortable zone', el.dataset.fdZone)
    }

    return instance
}

function unmountFdZone(el) {
    const instance = zoneRegistry.get(el)
    if (! instance) {
        return
    }

    instance.destroy()
    zoneRegistry.delete(el)
}

function ensureFdZones(root, commit) {
    if (! root || document.body.classList.contains('sorting')) {
        return
    }

    root.querySelectorAll('[data-fd-zone]').forEach((el) => {
        if (el.isConnected) {
            mountFdZone(el, commit)
        }
    })
}

function remountFdZones(root, commit) {
    if (! root || document.body.classList.contains('sorting')) {
        return
    }

    root.querySelectorAll('[data-fd-zone]').forEach((el) => {
        unmountFdZone(el)
        mountFdZone(el, commit)
    })
}

window.__spiggleFdMountZone = mountFdZone
window.__spiggleFdUnmountZone = unmountFdZone
window.__spiggleFdEnsureZones = ensureFdZones
window.__spiggleFdRemountZones = remountFdZones
window.__spiggleFdForceEndSort = forceEndActiveSort

registerMorphGuards()

document.addEventListener('livewire:navigating', clearStuckSortableState)
