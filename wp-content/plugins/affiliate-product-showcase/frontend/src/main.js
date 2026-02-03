import './style.css';
import { createIcons } from 'lucide';

// Initialize Lucide icons
createIcons();

// Tab interaction
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.tab').forEach(t => {
      t.classList.remove('tab-active');
      t.classList.add('bg-gray-100', 'text-gray-600');
    });
    this.classList.add('tab-active');
    this.classList.remove('bg-gray-100', 'text-gray-600');
  });
});

// Tag interaction
document.querySelectorAll('.tag').forEach(tag => {
  tag.addEventListener('click', function() {
    this.classList.toggle('tag-active');
  });
});

// Clear all functionality
document.getElementById('clearAll').addEventListener('click', function(e) {
  e.preventDefault();
  
  // Reset tabs
  document.querySelectorAll('.tab').forEach(t => {
    t.classList.remove('tab-active');
    t.classList.add('bg-gray-100', 'text-gray-600');
  });
  document.querySelector('.tab[data-category="all"]').classList.add('tab-active');
  document.querySelector('.tab[data-category="all"]').classList.remove('bg-gray-100', 'text-gray-600');
  
  // Reset tags
  document.querySelectorAll('.tag').forEach(t => {
    t.classList.remove('tag-active');
  });
});

// Bookmark toggle
document.querySelectorAll('.bookmark-icon').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    const icon = this.querySelector('svg');
    if (icon.classList.contains('fill-current')) {
      icon.classList.remove('fill-current', 'text-blue-500');
      icon.classList.add('text-gray-500');
    } else {
      icon.classList.add('fill-current', 'text-blue-500');
      icon.classList.remove('text-gray-500');
    }
  });
});
