// js/script.js

document.addEventListener("DOMContentLoaded", function() {
    // Example: add any custom JS if needed.
    var carImages = document.querySelectorAll(".car-images img");
    carImages.forEach(function(img) {
        img.addEventListener("click", function() {
            // For instance, you could add a lightbox feature here.
            alert("You clicked on the car image!");
        });
    });
});
