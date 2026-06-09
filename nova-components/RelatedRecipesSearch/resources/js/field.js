import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-related-recipes-search', IndexField)
  app.component('detail-related-recipes-search', DetailField)
  app.component('form-related-recipes-search', FormField)
})
