export const TemplateModule = {
    card: `<div class="card" >
              <div class="card-body">
<!--              style="width: 18rem;"-->
                <h5 class="card-title">---lastname---</h5>
                <h6 class="card-subtitle mb-2 text-muted">---firstname---</h6>
                <p class="card-text">---text---</p>
                <div class="row justify-content-between">
                    <a href="---link-show-customer---" class="card-link btn btn-outline-primary">Show customer</a>
                    <a href="---link-show-customer-carts---" data-idcustomer="---id---" class="customer-details btn btn-outline-primary">Choisir</a>
                </div>
              </div>
            </div>`,

    table: `<td class="cart-id">---cartId---</td>
            <td class="cart-date">---cartDate---</td>
            <td class="cart-total">---totalCart---</td>`,

};