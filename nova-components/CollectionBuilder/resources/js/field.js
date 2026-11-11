import GroupsField from './components/GroupsField'
import ItemsField from './components/ItemsField'

Nova.booting((app, store) => {
  app.component('form-collection-groups', GroupsField)
  app.component('form-collection-items', ItemsField)
})
