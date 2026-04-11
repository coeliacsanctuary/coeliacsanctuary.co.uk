import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-eatery-collections-query-builder', IndexField)
  app.component('detail-eatery-collections-query-builder', DetailField)
  app.component('form-eatery-collections-query-builder', FormField)
  // app.component('preview-eatery-collections-query-builder', PreviewField)
})
