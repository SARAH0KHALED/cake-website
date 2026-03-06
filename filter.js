// filter.js - Dynamic Recipe Filtering

// Global variables
let recipeElements = [];
let activeFilters = new Set();

// Initialize filtering system
function initializeFilter() {
  // Store all recipe elements
  recipeElements = Array.from(document.querySelectorAll('.recipe-card'));
  
  // Set up filter buttons
  const filterButtons = document.querySelectorAll('.filter-button');
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const category = this.getAttribute('data-category');
      toggleFilter(category, this);
      applyFilters();
    });
  });
  
  // Clear all filters button
  const clearFiltersBtn = document.getElementById('clear-filters');
  if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', clearAllFilters);
  }
}

// Toggle filter state
function toggleFilter(category, buttonElement) {
  if (activeFilters.has(category)) {
    activeFilters.delete(category);
    buttonElement.classList.remove('active');
  } else {
    activeFilters.add(category);
    buttonElement.classList.add('active');
  }
  
  // Update filter tags display
  updateFilterTags();
}

// Apply all active filters
function applyFilters() {
  if (activeFilters.size === 0) {
    // Show all recipes if no filters are active
    recipeElements.forEach(recipe => {
      showRecipeWithAnimation(recipe);
    });
    return;
  }
  
  // Filter recipes based on active filters
  recipeElements.forEach(recipe => {
    const categories = recipe.getAttribute('data-categories').split(',');
    
    // Check if recipe has at least one of the active filters
    const shouldShow = Array.from(activeFilters).some(filter => 
      categories.includes(filter)
    );
    
    if (shouldShow) {
      showRecipeWithAnimation(recipe);
    } else {
      hideRecipeWithAnimation(recipe);
    }
  });
}

// Show recipe with animation
function showRecipeWithAnimation(recipeElement) {
  // First set opacity to 0 but display to grid/block
  recipeElement.style.display = '';
  recipeElement.style.opacity = '0';
  
  // Force reflow
  void recipeElement.offsetWidth;
  
  // Add transition and set opacity to 1
  recipeElement.style.transition = 'opacity 0.3s ease-in';
  recipeElement.style.opacity = '1';
}

// Hide recipe with animation
function hideRecipeWithAnimation(recipeElement) {
  // Set opacity to 0 with transition
  recipeElement.style.transition = 'opacity 0.3s ease-out';
  recipeElement.style.opacity = '0';
  
  // After animation completes, set display to none
  setTimeout(() => {
    recipeElement.style.display = 'none';
  }, 300);
}

// Update filter tags display
function updateFilterTags() {
  const filterTagsContainer = document.getElementById('active-filters');
  if (!filterTagsContainer) return;
  
  // Clear existing tags
  filterTagsContainer.innerHTML = '';
  
  // If no active filters, hide the container
  if (activeFilters.size === 0) {
    filterTagsContainer.style.display = 'none';
    return;
  }
  
  // Show the container
  filterTagsContainer.style.display = 'flex';
  
  // Add tag for each active filter
  activeFilters.forEach(filter => {
    const tag = document.createElement('span');
    tag.className = 'filter-tag';
    tag.textContent = filter;
    
    const removeButton = document.createElement('button');
    removeButton.className = 'remove-filter';
    removeButton.innerHTML = '&times;';
    removeButton.addEventListener('click', () => {
      // Find and click the corresponding filter button to toggle it off
      const filterButton = document.querySelector(`.filter-button[data-category="${filter}"]`);
      if (filterButton) {
        filterButton.click();
      }
    });
    
    tag.appendChild(removeButton);
    filterTagsContainer.appendChild(tag);
  });
}

// Clear all active filters
function clearAllFilters() {
  const activeFilterButtons = document.querySelectorAll('.filter-button.active');
  activeFilterButtons.forEach(button => {
    button.classList.remove('active');
  });
  
  activeFilters.clear();
  updateFilterTags();
  applyFilters();
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeFilter);
