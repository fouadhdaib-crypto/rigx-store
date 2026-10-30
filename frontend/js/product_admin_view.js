const modal = document.getElementById('product-modal');
const form = document.querySelector('.add-product-form');
const openButton = document.getElementById('add-product-btn');
const closeButton = document.getElementById('exit-add-product-form-btn');
const fileInput = document.getElementById('product_img_file');
const imagePath = document.getElementById('product_img');
const preview = document.getElementById('product-img-preview');
const placeholder = document.getElementById('product-img-placeholder');

const showPreview = (source) => {
    if (source) {
        preview.src = source;
        preview.hidden = false;
        placeholder.hidden = true;
    } else {
        preview.removeAttribute('src');
        preview.hidden = true;
        placeholder.hidden = false;
    }
};

const resetForm = () => {
    form.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach((field) => {
        field.value = '';
    });

    document.getElementById('condition_type').selectedIndex = 0;
    document.getElementById('listing_status').value = 'available';

    imagePath.value = '';
    fileInput.value = '';
    showPreview('');

    const productId = form.querySelector('input[name="productId"]');

    if (productId) {
        productId.remove();
    }

    form.querySelector('.form-header h2').textContent = 'Add product';

    const submitButton = form.querySelector('.form-actions button[type="submit"]');

    submitButton.name = 'add_product';
    submitButton.textContent = 'Add Product';
};

const openForm = () => {
    modal.classList.add('open');
};

const closeForm = () => {
    modal.classList.remove('open');
};

openButton.addEventListener('click', () => {
    resetForm();
    openForm();
    document.getElementById('category_id').focus();
});

closeButton.addEventListener('click', closeForm);

modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeForm();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeForm();
    }
});

fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];

    if (file) {
        showPreview(URL.createObjectURL(file));
    } else {
        showPreview(imagePath.value ? '../' + imagePath.value : '');
    }
});