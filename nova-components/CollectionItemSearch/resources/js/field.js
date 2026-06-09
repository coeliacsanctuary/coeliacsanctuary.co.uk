import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-collection-item-search', IndexField)
  app.component('detail-collection-item-search', DetailField)
  app.component('form-collection-item-search', FormField)
  app.component('preview-collection-item-search', PreviewField)
})
