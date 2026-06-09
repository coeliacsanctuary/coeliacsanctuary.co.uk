document.addEventListener('inertia:navigate', (event) => {
    const flash = event.detail.page.props.novaFlash

    if (flash) {
        Nova.success(flash)
    }
})
