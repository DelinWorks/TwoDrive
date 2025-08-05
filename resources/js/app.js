require('./bootstrap');

function getFileExtension(filename) {
    const parts = filename.split(".");
    return parts.length > 1 ? parts.pop().toLowerCase() : "default";
}

window.getFileExtension = getFileExtension;

// Save scrollY
window.addEventListener('beforeunload', () => {
    sessionStorage.setItem('scrollY', window.scrollY);
});

// Restore scroll on load
window.addEventListener('load', () => {
    const scrollY = sessionStorage.getItem('scrollY');
    if (scrollY !== null) {
        window.scrollTo(0, parseInt(scrollY));
    }
});

function toggleAll() {
    window.dispatchEvent(new CustomEvent('checkbox-external-update', { detail: window.all = !window.all }));
}

window.toggleAll = toggleAll;
window.all = false

function fileSize(bytes) {
    if (bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const size = bytes / Math.pow(1024, i);
    return size.toFixed(1) + ' ' + units[i];
}

window.fileSize = fileSize;
