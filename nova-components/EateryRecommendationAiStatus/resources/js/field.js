import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-eatery-recommendation-ai-status', IndexField)
  app.component('detail-eatery-recommendation-ai-status', DetailField)
  app.component('form-eatery-recommendation-ai-status', FormField)
  // app.component('preview-eatery-recommendation-ai-status', PreviewField)
})
