document.addEventListener("DOMContentLoaded", function() {
    // For car gallery images (if present) inside an element with class "car-images"
    var carImages = document.querySelectorAll(".car-images img");
    carImages.forEach(function(img) {
        img.addEventListener("click", function() {
            openModal(this.src);
        });
    });

    // For license images in admin view (assuming they have alt="Driving License")
    var licenseImages = document.querySelectorAll("img[alt='Driving License']");
    licenseImages.forEach(function(img) {
        img.addEventListener("click", function() {
            openModal(this.src);
        });
    });

    // Add click event to the modal itself to close if clicking outside the image
    var modal = document.getElementById("myModal");
    if (modal) {
        modal.addEventListener("click", function(e) {
            // Close if the click is on the modal background (not the image)
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});

// Function to open the modal with the given image source
function openModal(imageSrc) {
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("modalImg");
    if (modal && modalImg) {
        modalImg.src = imageSrc;
        modal.style.display = "block";
    }
}

// Function to close the modal
function closeModal() {
    var modal = document.getElementById("myModal");
    if (modal) {
        modal.style.display = "none";
    }
}
