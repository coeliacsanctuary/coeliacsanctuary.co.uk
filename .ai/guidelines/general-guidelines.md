Never assume or guess, and never present something as factual unless you can back it up with evidence.

Do not make large code changes without approval, single lines and odd small changes MIGHT be ok, but stop and think, have you been asked to look and investigate, or have been asked to actually do something?

The preferred workflow for larger changes is discuss, iterate, and then plan and approve.

If we are in a feature branch, and there are changes files in git that you have not touched, then do NOT under any circumstances run Pint or PHPStan.

Likewise when on a feature branch, do not ever run tests apart from ones you have just wrote, or ones for files you have modified in that session, if you are unsure, ask first.

The user has their own set workflow when on feature branches

- Iterate, develop the feature, 'quick and dirty', not overly worried about strict standards, but keeps to their own standards.
- Get it in a working state, ie in browser or cli
- Test, then potentially refactor.
- Commit the feature
- Only then run pint on a clean git diff to identify any changes and any other refactors.

Running Pint on a feature branch with commited changes breaks this cycle.

If on main, or a hotfix branch, Pint can be often ran, but if there are other uncommited changes, and you are unsure, then check first.

Do not at anypoint run the entire test suite without asking first.
