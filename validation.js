// validation.js - Recipe Form Validation

// Main validation function
function validateRecipeForm() {
  let errors = [];

  // Validate required fields
  const requiredFields = {
    'recipe-title': 'Recipe title is required',
    'recipe-description': 'Recipe description is required',
    'preparation-time': 'Preparation time is required',
    'cooking-time': 'Cooking time is required',
    servings: 'Number of servings is required',
  };

  for (const [fieldId, errorMessage] of Object.entries(requiredFields)) {
    const field = document.getElementById(fieldId);
    if (!field || !field.value.trim()) {
      errors.push(errorMessage);
      highlightField(field, true);
    } else {
      highlightField(field, false);
    }
  }

  // Validate email format
  const emailField = document.getElementById('contact-email');
  if (emailField && emailField.value.trim()) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(emailField.value.trim())) {
      errors.push('Invalid email address format');
      highlightField(emailField, true);
    } else {
      highlightField(emailField, false);
    }
  }

  // Validate ingredients (at least one ingredient with name and quantity)
  const ingredientRows = document.querySelectorAll('.ingredient-row');
  let hasValidIngredient = false;

  ingredientRows.forEach(row => {
    const nameField = row.querySelector('.ingredient-name');
    const quantityField = row.querySelector('.ingredient-quantity');

    if (
      nameField &&
      quantityField &&
      nameField.value.trim() &&
      quantityField.value.trim()
    ) {
      hasValidIngredient = true;
      highlightField(nameField, false);
      highlightField(quantityField, false);
    } else if (
      nameField &&
      nameField.value.trim() &&
      (!quantityField || !quantityField.value.trim())
    ) {
      highlightField(nameField, false);
      highlightField(quantityField, true);
    }
  });

  if (!hasValidIngredient) {
    errors.push('At least one ingredient with name and quantity is required');
  }

  // Validate instructions (at least one step)
  const instructionSteps = document.querySelectorAll('.instruction-step');
  let hasValidInstruction = false;

  instructionSteps.forEach(step => {
    if (step.value.trim()) {
      hasValidInstruction = true;
      highlightField(step, false);
    }
  });

  if (!hasValidInstruction) {
    errors.push('At least one instruction step is required');
  }

  // Show error popup if there are errors
  if (errors.length > 0) {
    showErrorPopup(errors);
    return false;
  }

  return true;
}

// Highlight invalid fields
function highlightField(field, isError) {
  if (!field) return;

  if (isError) {
    field.classList.add('error-field');
    field.classList.remove('valid-field');
  } else {
    field.classList.remove('error-field');
    field.classList.add('valid-field');
  }
}

// Show error popup with all validation errors
function showErrorPopup(errors) {
  // Create popup container
  const popup = document.createElement('div');
  popup.className = 'error-popup';

  // Create popup content
  const popupContent = document.createElement('div');
  popupContent.className = 'error-popup-content';

  // Create header
  const header = document.createElement('div');
  header.className = 'error-header';
  header.textContent = 'Please fix the following errors:';

  // Create error list
  const errorList = document.createElement('ul');
  errors.forEach(error => {
    const listItem = document.createElement('li');
    listItem.textContent = error;
    errorList.appendChild(listItem);
  });

  // Create close button
  const closeButton = document.createElement('button');
  closeButton.className = 'error-close-btn';
  closeButton.textContent = 'Close';
  closeButton.onclick = function () {
    document.body.removeChild(popup);
  };

  // Assemble popup
  popupContent.appendChild(header);
  popupContent.appendChild(errorList);
  popupContent.appendChild(closeButton);
  popup.appendChild(popupContent);

  // Add popup to body
  document.body.appendChild(popup);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function () {
  const recipeForm = document.getElementById('recipe-form');
  if (recipeForm) {
    recipeForm.addEventListener('submit', function (event) {
      if (!validateRecipeForm()) {
        event.preventDefault();
      }
    });
  }

  // Real-time validation for important fields
  const emailField = document.getElementById('contact-email');
  if (emailField) {
    emailField.addEventListener('blur', function () {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      highlightField(
        emailField,
        emailField.value.trim() && !emailPattern.test(emailField.value.trim())
      );
    });
  }
});
