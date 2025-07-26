import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-hidden-writable-field', IndexField)
  app.component('detail-hidden-writable-field', DetailField)
  app.component('form-hidden-writable-field', FormField)
  // app.component('preview-hidden-writable-field', PreviewField)
})
