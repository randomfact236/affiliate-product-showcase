const fs = require('fs');

const filesToDelete = [
  'tmp_check_diff.js',
  'tmp_compare_plugin_tree.js',
  'tmp_compare_plugin_tree_hier.js',
  'tmp_plan_1_2_audit.js',
  'tmp_todo_generated.md'
];

filesToDelete.forEach(file => {
  if (fs.existsSync(file)) {
    fs.unlinkSync(file);
    console.log('Deleted: ' + file);
  } else {
    console.log('Not found (skipped): ' + file);
  }
});
