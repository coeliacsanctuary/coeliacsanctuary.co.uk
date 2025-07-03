import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-shop-generate-resend-slip-button', IndexField)
  app.component('detail-shop-generate-resend-slip-button', DetailField)
  app.component('form-shop-generate-resend-slip-button', FormField)
  // app.component('preview-shop-generate-resend-slip-button', PreviewField)
})
