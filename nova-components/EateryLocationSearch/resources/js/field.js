import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-eatery-location-search', IndexField)
  app.component('detail-eatery-location-search', DetailField)
  app.component('form-eatery-location-search', FormField)
  // app.component('preview-eatery-location-search', PreviewField)
})
