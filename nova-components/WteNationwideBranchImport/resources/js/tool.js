import Index from './pages/Index';
import Processed from './pages/Processed';
import Complete from './pages/Complete';

Nova.inertia('WteNationwideBranchImport.Index', Index);
Nova.inertia('WteNationwideBranchImport.Processed', Processed);
Nova.inertia('WteNationwideBranchImport.Complete', Complete);

Nova.booting((app, store) => {
  //
});
