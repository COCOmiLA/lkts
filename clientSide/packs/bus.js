import mitt from 'mitt'

const emitter = mitt()

$(document).ready(function () {
    window.bus = emitter
});

