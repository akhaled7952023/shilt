function generateSlugFromTitle(title, targetSlugId) {
    title = title.trim();

    title = title.replace(/\s+/g, '-');

    if (title === '') {
        return;
    }

    title = title.toLowerCase();

    document.getElementById(targetSlugId).value = title;
}
