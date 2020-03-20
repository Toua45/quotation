export const TemplateModule = {
    card: `<div class="card" style="width: 18rem;">
              <div class="card-body">

                <h5 class="card-title">---lastname---</h5>
                <h6 class="card-subtitle mb-2 text-muted">---firstname---</h6>
                <p class="card-text">---text---</p>
                <a href="---link-show-customer---" class="card-link">Show customer</a>
                <a href="---link-show-customer-carts---" data-idcustomer="---id---" class="customer-details btn btn-outline-primary ml-3">Choisir</a>
              </div>
            </div>`,

    table: `<td>---cartId---</td>
            <td>---cartDate---</td>
            <td>---totalCart---</td>`,

};