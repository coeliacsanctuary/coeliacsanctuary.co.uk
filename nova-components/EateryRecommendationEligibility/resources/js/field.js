import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-eatery-recommendation-eligibility', IndexField)
  app.component('detail-eatery-recommendation-eligibility', DetailField)
  app.component('form-eatery-recommendation-eligibility', FormField)
  // app.component('preview-eatery-recommendation-eligibility', PreviewField)
})
