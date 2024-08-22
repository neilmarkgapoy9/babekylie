const checkboxes = document.querySelectorAll('input[name="amount"]');
const amountDisplay = document.getElementById('amount-display');
const selectedAmount = document.getElementById('selected-amount');
const paypalButtonContainer = document.getElementById('paypal-button-container');

function createPayPalButton(serverAmount) {
    if (paypalButtonContainer.firstChild) {
        paypalButtonContainer.innerHTML = '';
    }

    paypal.Buttons({
        createOrder: function(data, actions) {
            return fetch('/.netlify/functions/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ amount: serverAmount })
            })
            .then(response => response.json())
            .then(order => order.id);
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                alert('Transaction completed by ' + details.payer.name.given_name);
            });
        }
    }).render('#paypal-button-container');
}

function updateUI(amount, serverAmount) {
    selectedAmount.textContent = amount;
    amountDisplay.classList.remove('hidden');
    paypalButtonContainer.classList.remove('hidden');
    createPayPalButton(serverAmount);
}

checkboxes.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.checked) {
            const amount = this.value;
            const serverAmount = this.getAttribute('data-server-amount');
            updateUI(amount, serverAmount);
        } else {
            amountDisplay.classList.add('hidden');
            paypalButtonContainer.classList.add('hidden');
        }
    });
});

document.querySelector('input[name="amount"]:checked')?.dispatchEvent(new Event('change'));
