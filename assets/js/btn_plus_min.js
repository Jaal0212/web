//incrementar cantidad input
const btnMinus = document.getElementById("min");
const btnPlus = document.getElementById("plus");

const pricePerItem = 150;  // Precio unitario del producto
const quantityInput = document.getElementById("quantity-input"); // Campo de cantidad
const totalPrice = document.getElementById("precioxcantidad");  // Total por producto
const totalSummary = document.getElementById("total-price"); // Total global

// Función para actualizar el precio total
function updateTotal() {
    let quantity = parseInt(quantityInput.value); // Obtener la cantidad
    if (quantity < 1) quantity = 1;  // Evitar que la cantidad sea menor que 1
    const itemTotal = pricePerItem * quantity;  // Calcular el total por producto
    totalPrice.textContent = "$" + itemTotal;  // Actualizar el total por producto
    totalSummary.textContent = "$" + itemTotal;  // Actualizar el total global   
}

// Llamar a la función cuando el valor del input cambie
quantityInput.addEventListener("input", updateTotal);

// Función para disminuir la cantidad
btnMinus.addEventListener("click", function () {
    let currentValue = parseInt(quantityInput.value); // Obtener el valor actual
    if (currentValue > 1) { // Asegurarse de que el valor no baje de 1
        quantityInput.value = currentValue - 1;
        updateTotal();
    }
});

// Función para aumentar la cantidad
btnPlus.addEventListener("click", function () {
    let currentValue = parseInt(quantityInput.value); // Obtener el valor actual
    quantityInput.value = currentValue + 1;
    updateTotal();
});