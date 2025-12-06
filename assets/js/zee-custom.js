'use strict';

function blockArea(elementToBlock) {
    elementToBlock.block({
        message: '<div class="spinner-border text-white" role="status"></div>',
        css: {
            backgroundColor: 'transparent',
            color: '#fff',
            border: '0'
        },
        overlayCSS: {
            opacity: 0.5,
            backgroundColor: '#934583'
        }
    });
}

function unBlockArea(elementToBlock) {
    elementToBlock.unblock();
}
