<script setup>
  import { onMounted, ref } from 'vue';
  import Uppy, { debugLogger } from '@uppy/core';
  import DragDrop from '@uppy/drag-drop';
  import Dashboard from '@uppy/dashboard';
  import XHR from '@uppy/xhr-upload';

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
    maxFiles: {
      type: Number,
      required: false,
      default: 1
    },
  })

  onMounted (async () => {
    const uppy = new Uppy({
      logger: debugLogger,
      meta: props.meta,
      restrictions: {
        maxNumberOfFiles: props.maxFiles
      }
    })
    .use(Dashboard, {
      inline: true,
      target: "#uppy",
      showProgressDetails: true,
      proudlyDisplayPoweredByUppy: false,
      note: 'Images JPG only, up to 1 MB',
      height: 200,
      width: "100%",
      locale: {
        strings: {
          dropPasteFiles: 'Lege Bilder deiner Unterkunft hier ab oder %{browseFiles} (max. 10 Bilder)',
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
