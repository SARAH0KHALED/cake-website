// search.js - Dynamic Recipe Search

// Global variables
let recipeData = [];
let searchTimer;
let searchResultsVisible = false;

// Initialize search functionality
function initializeSearch() {
  const searchInput = document.getElementById('recipe-search');
  const searchResults = document.getElementById('search-results');
  
  if (!searchInput || !searchResults) return;
  
  // Collect all recipe data on page load
  collectRecipeData();
  
  // Set up event listeners
  searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    
    // Clear previous timer
    clearTimeout(searchTimer);
    
    if (query.length < 2) {
      hideSearchResults(searchResults);
      return;
    }
    
    // Set small delay to avoid searching on every keystroke
    searchTimer = setTimeout(() => {
      const results = performSearch(query);
      displaySearchResults(results, searchResults, query);
    }, 300);
  });
  
  // Hide search results when clicking outside
  document.addEventListener('click', function(event) {
    if (searchResultsVisible && 
        !searchInput.contains(event.target) && 
        !searchResults.contains(event.target)) {
      hideSearchResults(searchResults);
    }
  });
  
  // Show results again when search input is focused
  searchInput.addEventListener('focus', function() {
    const query = this.value.trim().toLowerCase();
    if (query.length >= 2) {
      const results = performSearch(query);
      displaySearchResults(results, searchResults, query);
    }
  });
}

// Collect recipe data from the page
function collectRecipeData() {
  const recipeCards = document.querySelectorAll('.recipe-card');
  
  recipeData = Array.from(recipeCards).map(card => {
    const title = card.querySelector('.recipe-title')?.textContent || '';
    const description = card.querySelector('.recipe-description')?.textContent || '';
    const categories = card.getAttribute('data-categories')?.split(',') || [];
    const ingredients = Array.from(card.querySelectorAll('.recipe-ingredient')).map(ing => ing.textContent);
    const url = card.querySelector('a')?.getAttribute('href') || '#';
    const imageUrl = card.querySelector('img')?.getAttribute('src') || '';
    
    return {
      title,
      description,
      categories,
      ingredients,
      url,
      imageUrl,
      element: card
    };
  });
}

// Perform search on collected data
function performSearch(query) {
  if (query.length < 2) return [];
  
  // Split query into words for better matching
  const queryWords = query.toLowerCase().split(' ').filter(word => word.length > 0);
  
  return recipeData.filter(recipe => {
    // Check for matches in title, description, categories, and ingredients
    const titleMatch = recipe.title.toLowerCase().includes(query);
    const descriptionMatch = recipe.description.toLowerCase().includes(query);
    
    // Match any query word against categories
    const categoryMatch = recipe.categories.some(category => 
      queryWords.some(word => category.toLowerCase().includes(word))
    );
    
    // Match any query word against ingredients
    const ingredientMatch = recipe.ingredients.some(ingredient => 
      queryWords.some(word => ingredient.toLowerCase().includes(word))
    );
    
    return titleMatch || descriptionMatch || categoryMatch || ingredientMatch;
  }).slice(0, 8); // Limit to 8 results
}

// Display search results
function displaySearchResults(results, resultsContainer, query) {
  // Clear previous results
  resultsContainer.innerHTML = '';
  
  // Update visibility
  if (results.length === 0) {
    resultsContainer.innerHTML = `<div class="no-results">No recipes found for "${query}"</div>`;
  } else {
    results.forEach((recipe, index) => {
      const resultItem = createSearchResultItem(recipe, query);
      resultsContainer.appendChild(resultItem);
      
      // Add slight delay for animation
      setTimeout(() => {
        resultItem.classList.add('visible');
      }, index * 50);
    });
  }
  
  // Show results container
  resultsContainer.style.display = 'block';
  void resultsContainer.offsetWidth; // Force reflow
  resultsContainer.classList.add('active');
  searchResultsVisible = true;
}

// Create a search result item
function createSearchResultItem(recipe, query) {
  const resultItem = document.createElement('div');
  resultItem.className = 'search-result-item';
  
  // Create image thumbnail if available
  if (recipe.imageUrl) {
    const thumbnail = document.createElement('div');
    thumbnail.className = 'result-thumbnail';
    thumbnail.style.backgroundImage = `url('${recipe.imageUrl}')`;
    resultItem.appendChild(thumbnail);
  }
  
  // Create content container
  const content = document.createElement('div');
  content.className = 'result-content';
  
  // Title with highlighted query
  const title = document.createElement('h4');
  title.className = 'result-title';
  title.innerHTML = highlightText(recipe.title, query);
  content.appendChild(title);
  
  // Brief description
  if (recipe.description) {
    const description = document.createElement('p');
    description.className = 'result-description';
    const shortDesc = recipe.description.substring(0, 80) + (recipe.description.length > 80 ? '...' : '');
    description.innerHTML = highlightText(shortDesc, query);
    content.appendChild(description);
  }
  
  // Categories
  if (recipe.categories.length > 0) {
    const categories = document.createElement('div');
    categories.className = 'result-categories';
    categories.textContent = recipe.categories.slice(0, 3).join(', ');
    content.appendChild(categories);
  }
  
  resultItem.appendChild(content);
  
  // Add click event
  resultItem.addEventListener('click', () => {
    window.location.href = recipe.url;
  });
  
  return resultItem;
}

// Highlight matching text
function highlightText(text, query) {
  // Escape special regex characters from the query
  const safeQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const regex = new RegExp(`(${safeQuery})`, 'gi');
  return text.replace(regex, '<span class="highlight">$1</span>');
}

// Hide search results
function hideSearchResults(resultsContainer) {
  resultsContainer.classList.remove('active');
  searchResultsVisible = false;
  
  // After animation completes, hide the container
  setTimeout(() => {
    resultsContainer.style.display = 'none';
    resultsContainer.innerHTML = '';
  }, 300);
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeSearch);