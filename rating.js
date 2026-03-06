// rating.js - Recipe Rating System

// Initialize rating system for all recipes
function initializeRatings() {
  const ratingContainers = document.querySelectorAll('.rating-container');
  
  ratingContainers.forEach(container => {
    const recipeId = container.getAttribute('data-recipe-id');
    const stars = container.querySelectorAll('.star');
    const ratingDisplay = container.querySelector('.rating-value');
    const ratingCount = container.querySelector('.rating-count');
    
    // Set up event listeners for each star
    stars.forEach((star, index) => {
      // Hover effects
      star.addEventListener('mouseenter', () => {
        highlightStars(stars, index);
      });
      
      // Click to rate
      star.addEventListener('click', () => {
        const rating = index + 1;
        submitRating(recipeId, rating);
        setCurrentRating(stars, index);
        updateRatingDisplay(ratingDisplay, rating);
        updateRatingCount(ratingCount);
        
        // Add visual feedback
        showRatingConfirmation(container, rating);
      });
    });
    
    // Reset stars when mouse leaves container
    container.addEventListener('mouseleave', () => {
      const currentRating = parseInt(container.getAttribute('data-current-rating') || '0');
      resetStars(stars, currentRating - 1);
    });
    
    // Set initial rating if exists
    const initialRating = parseInt(container.getAttribute('data-initial-rating') || '0');
    if (initialRating > 0) {
      setCurrentRating(stars, initialRating - 1);
      container.setAttribute('data-current-rating', initialRating.toString());
    }
  });
}

// Highlight stars on hover
function highlightStars(stars, activeIndex) {
  stars.forEach((star, index) => {
    if (index <= activeIndex) {
      star.classList.add('hover');
    } else {
      star.classList.remove('hover');
    }
  });
}

// Reset stars to current rating
function resetStars(stars, activeIndex) {
  stars.forEach((star, index) => {
    star.classList.remove('hover');
    if (index <= activeIndex) {
      star.classList.add('active');
    } else {
      star.classList.remove('active');
    }
  });
}

// Set current rating
function setCurrentRating(stars, activeIndex) {
  stars.forEach((star, index) => {
    if (index <= activeIndex) {
      star.classList.add('active');
    } else {
      star.classList.remove('active');
    }
  });
}

// Submit rating to server (AJAX)
function submitRating(recipeId, rating) {
  // Store rating in localStorage for demo purposes
  // In a real application, this would be an AJAX call to your server
  const storedRatings = JSON.parse(localStorage.getItem('recipeRatings') || '{}');
  
  if (!storedRatings[recipeId]) {
    storedRatings[recipeId] = {
      sum: 0,
      count: 0,
      average: 0
    };
  }
  
  storedRatings[recipeId].sum += rating;
  storedRatings[recipeId].count += 1;
  storedRatings[recipeId].average = storedRatings[recipeId].sum / storedRatings[recipeId].count;
  
  localStorage.setItem('recipeRatings', JSON.stringify(storedRatings));
  
  // Update all instances of this recipe's rating on the page
  updateRecipeRatings(recipeId, storedRatings[recipeId]);
}

// Update recipe ratings across the page
function updateRecipeRatings(recipeId, ratingData) {
  const allRatingContainers = document.querySelectorAll(`.rating-container[data-recipe-id="${recipeId}"]`);
  
  allRatingContainers.forEach(container => {
    const ratingDisplay = container.querySelector('.rating-value');
    const ratingCount = container.querySelector('.rating-count');
    
    if (ratingDisplay) {
      updateRatingDisplay(ratingDisplay, ratingData.average);
    }
    
    if (ratingCount) {
      ratingCount.textContent = `(${ratingData.count} ${ratingData.count === 1 ? 'rating' : 'ratings'})`;
    }
    
    // Update container data attribute
    container.setAttribute('data-current-rating', Math.round(ratingData.average).toString());
  });
}

// Update rating display
function updateRatingDisplay(displayElement, rating) {
  if (displayElement) {
    displayElement.textContent = rating.toFixed(1);
  }
}

// Update rating count
function updateRatingCount(countElement) {
  if (countElement) {
    const currentCount = parseInt(countElement.textContent.match(/\d+/) || '0');
    const newCount = currentCount + 1;
    countElement.textContent = `(${newCount} ${newCount === 1 ? 'rating' : 'ratings'})`;
  }
}

// Show rating confirmation animation
function showRatingConfirmation(container, rating) {
  const confirmation = document.createElement('div');
  confirmation.className = 'rating-confirmation';
  confirmation.textContent = `Thanks for rating ${rating}/5!`;
  
  container.appendChild(confirmation);
  
  // Animate
  setTimeout(() => {
    confirmation.classList.add('show');
  }, 10);
  
  // Remove after animation
  setTimeout(() => {
    confirmation.classList.remove('show');
    setTimeout(() => {
      container.removeChild(confirmation);
    }, 300);
  }, 2000);
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeRatings);