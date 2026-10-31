"use strict";

(function () {
  const form = document.getElementById("pcBuilderForm");
  const purposeInputs = document.querySelectorAll('input[name="purpose"]');
  const purposeCards = document.querySelectorAll(".pb-purpose-option");
  const resolutionGroup = document.getElementById("pbResolutionGroup");
  const submitButton = document.getElementById("pbGenerateButton");

  function updatePurposeUI() {
    let selectedPurpose = "gaming";

    purposeInputs.forEach(function (input) {
      if (input.checked) {
        selectedPurpose = input.value;
      }
    });

    purposeCards.forEach(function (card) {
      const input = card.querySelector('input[name="purpose"]');

      card.classList.toggle("selected", Boolean(input && input.checked));
    });

    if (resolutionGroup) {
      resolutionGroup.hidden = selectedPurpose !== "gaming";
    }
  }

  purposeInputs.forEach(function (input) {
    input.addEventListener("change", updatePurposeUI);
  });

  updatePurposeUI();

  if (form && submitButton) {
    form.addEventListener("submit", function () {
      if (!form.reportValidity()) {
        return;
      }

      submitButton.disabled = true;

      const buttonText = submitButton.querySelector("span:first-child");

      if (buttonText) {
        buttonText.textContent = "Generating your build…";
      }
    });
  }

  const results = document.getElementById("build-results");

  if (results && window.location.hash === "#build-results") {
    window.requestAnimationFrame(function () {
      results.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    });
  }
})();
