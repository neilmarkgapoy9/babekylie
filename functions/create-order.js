const fetch = require('node-fetch');

exports.handler = async (event, context) => {
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            body: JSON.stringify({ error: 'Method Not Allowed' })
        };
    }

    const { amount } = JSON.parse(event.body);

    if (![100, 190, 270].includes(Number(amount))) {
        return {
            statusCode: 400,
            body: JSON.stringify({ error: 'Invalid amount' })
        };
    }

    const PAYPAL_CLIENT_ID = process.env.PAYPAL_CLIENT_ID;
    const PAYPAL_CLIENT_SECRET = process.env.PAYPAL_CLIENT_SECRET;

    const response = await fetch('https://api-m.sandbox.paypal.com/v2/checkout/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Basic ${Buffer.from(`${PAYPAL_CLIENT_ID}:${PAYPAL_CLIENT_SECRET}`).toString('base64')}`
        },
        body: JSON.stringify({
            purchase_units: [{
                amount: {
                    currency_code: 'USD',
                    value: amount
                }
            }]
        })
    });

    const data = await response.json();
    return {
        statusCode: response.status,
        body: JSON.stringify(data)
    };
};
