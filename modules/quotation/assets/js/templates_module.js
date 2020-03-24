export const TemplateModule = {
    card: `<div id='customer-card_---increment---' class="card hidden mr-3 mb-4" >
              <div class="card-body">
                <h5 class="card-title">---lastname---</h5>
                <h6 class="card-subtitle mb-2 text-muted">---firstname---</h6>
                <p class="card-text">---text---</p>
                <div class="row justify-content-between">
                    <a href="---link-show-customer---" class="card-link btn btn-outline-primary mx-3">Show customer</a>
                    <a href="---link-show-customer-carts---" data-idcustomer="---id---" class="customer-details btn btn-outline-primary mx-3">Choisir</a>
                </div>
              </div>
            </div>`,

    tableCart: `<tr>
            <td class="cart-id text-left">---cartId---</td>
            <td class="cart-date text-left">---cartDate---</td>
            <td class="cart-total text-left">---totalCart---</td>
            </tr>`,

    tableOrder: `<tr>
            <td class="order-id text-left">---orderId---</td>
            <td class="order-date text-left">---orderDate---</td>
            <td class="order-total text-left">---totalOrder---</td>
            <td class="order-payment text-left">---payment---</td>
        </tr>`,

    tableQuotation: `<tr>
            <td class="quotation-id text-left">---quotationId---</td>
            <td class="quotation-date text-left">---quotationDate---</td>
            <td class="quotation-total text-left">---totalQuotation---</td>
        </tr>`,
};
