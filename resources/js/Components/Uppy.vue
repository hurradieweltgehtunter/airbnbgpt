<script setup>
  import { onMounted, ref } from 'vue';
  import Uppy, { debugLogger } from '@uppy/core';
  import DragDrop from '@uppy/drag-drop';
  import Dashboard from '@uppy/dashboard';
  import XHR from '@uppy/xhr-upload';
  import German from '@uppy/locales/lib/de_DE';

  // Don't forget the CSS: core and UI components + plugins you are using
  import '@uppy/core/dist/style.css';
  import '@uppy/dashboard/dist/style.css';
  import '@uppy/webcam/dist/style.css';

  const props = defineProps({
    meta: {
      type: Object,
      required: false
    },
    XHRconfig: {
      type: Object,
      required: false
    },
    onUploadSuccess: {
      type: Function,
      required: false,
      default: () => {
        console.log('onUploadSuccess');
      }
    },
    onUploadComplete: {
      type: Function,
      required: false,
      default: () => {
        console.log('onUploadComplete');
      }
    },
    onProgress: {
      type: Function,
      required: false,
      default: (progress) => {
        console.log('onProgress: ', progress);
      }
    },
    maxNumberOfFiles: {
      type: Number,
      required: false,
      default: 1
    },
    maxFileSize: {
      type: Number,
      required: false,
      default: 4194304 // 4MB: If you change this, change also the backend validation
    },
    allowedFileTypes: {
      type: Array,
      required: false,
      default: ['image/jpg', 'image/jpeg', 'image/png']
    },
  })

  onMounted (async () => {
    const uppy = new Uppy({
      logger: debugLogger,
      locale: German,
      meta: props.meta,
      restrictions: {
        maxNumberOfFiles: props.maxNumberOfFiles,
        maxFileSize: props.maxFileSize,
        allowedFileTypes: props.allowedFileTypes
      }
    })
    .use(Dashboard, {
      inline: true,
      target: "#uppy",
      showProgressDetails: true,
      proudlyDisplayPoweredByUppy: false,
      note: 'Images JPG only, up to 4 MB',
      height: 200,
      width: "100%",
      locale: {
        strings: {
          dropPasteFiles: 'Lege Bilder deiner Unterkunft hier ab oder %{browseFiles} (max. 10 Bilder, max.4MB pro Bild)',
          browseFiles: 'wähle Bilder aus'
        }
      }
    })
    .use(XHR, props.XHRconfig)

    uppy.on('progress', props.onProgress);

    uppy.on('complete', (result) => {
      props.onUploadComplete(result);
      uppy.cancelAll();
    });

    uppy.on('upload-success', props.onUploadSuccess);

    uppy.on('upload-error', (file, error, response) => {
      console.log('error with file:', file.id);
      console.log('error message:', error);
    });
  });
</script>

<template>
  <div class="w-full" id="uppy"></div>
</template>

<style>
/* Your component styles here */
</style>
