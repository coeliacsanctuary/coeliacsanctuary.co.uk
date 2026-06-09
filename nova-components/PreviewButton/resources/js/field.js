import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-preview-button', IndexField)
  app.component('detail-preview-button', DetailField)
  app.component('form-preview-button', FormField)
})
