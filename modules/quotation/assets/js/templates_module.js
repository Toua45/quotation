export const TemplateModule = {

    card: `<div id='customer-card_---increment---' class="card hidden customerCard" >
                <div class="card-header card-header-customer">    
                    <h2>---firstname--- ---lastname---</h2>
                    <h2>#---customer-id---</h2>             
                </div>
                <div class="card-body">
                    <p class="card-text">---email---</p>
                    <p class="text-muted">---birthday---</p>
                </div>

                <div class="card-footer">
                    <button class="customer-show btn btn-primary" data-href="link-show-customer" data-idcustomer="---id-customer---" data-toggle="modal" data-target="#showCustomerModal_---id-customer-modal---">
                        <span uk-icon="icon: search" class="mr-1 search-icon"></span> Details
                    </button>  
                    <a href="---link-show-customer-carts---" data-idcustomer="---id---" class="customer-details btn btn-outline-primary">
                        <span uk-icon="icon: arrow-right" class="mr-1"></span> Choisir
                    </a>
                </div>
                    
                    ---modal-customer-infos---         
            </div>`,

    modalCustomerInfos: `<div class="modal fade customerModal" id="showCustomerModal_---id-customer-modal---" tabindex="-1" role="dialog" aria-labelledby="showCustomerModalTitle" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                  
                                    <div class="modal-body">                              
                                        <div class="card">---personal-datas---</div>
                                        <div class="d-flex ordersAndCarts">
                                            <div class="card mr-2">---customer-orders---</div>   
                                            <div class="card">---customer-carts---</div>   
                                        </div>
                                        <div class="card">---customer-addresses---</div>   
                                    </div>
                                </div>
                              </div>
                         </div>`,

    personalData: `<div id="modal-personal-data-infos_---id-customer-modal---">
                        <div class="col">
                              <h3 class="card-header"> <i class="material-icons">person</i>
                                ---firstname--- ---lastname--- [---id-customer---] -
                                <a href="---customer-link-email---">---customer-email---</a>
                                <a href="---edit---" class="tooltip-link float-right" data-toggle="pstooltip" target="_blank" data-placement="top" data-original-title="Modifier">
                                  <i class="material-icons">edit</i>
                                </a>
                              </h3>
                              
                              <div class="card-body">
                                <div class="row mb-1">
                                    <div class="col-4 text-right">Titre de civilité</div>
                                    <div class="col-8">---gender---</div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-4 text-right">Âge</div>
                                    <div class="col-8">---old--- ans (date de naissance : ---birthday---)</div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-4 text-right">Date d'inscription</div>
                                    <div class="col-8">---registration---</div>
                                </div>                         
                                
                                <div class="row mb-1">
                                    <div class="col-4 text-right">Langue</div>
                                    <div class="col-8">---lang---</div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-4 text-right">Inscriptions</div>
                                    
                                    <div class="col-8">                                            
                                        <span class="badge ---badge-newsletter--- rounded pt-0 pb-0">      
                                          <i class="material-icons">---icon-newsletter---</i> Lettre d'informations
                                        </span>
                            
                                        <span class="badge ---badge-partners--- rounded pt-0 pb-0">
                                            <i class="material-icons">---icon-partners---</i> Offres partenaires
                                        </span>
                                    </div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-4 text-right">Dernière mise à Jour</div>
                                    <div class="col-8">---last-update---</div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-4 text-right">État</div>
                                    <div class="col-8">
                                        <span class="badge ---badge-is-active--- rounded pt-0 pb-0">
                                            <i class="material-icons">---icon-is-active---</i> ---is-active---
                                        </span>
                                    </div>
                                </div>
                              </div>
                              
                        </div>
                    </div>`,

    customerOrders: `<div id="modal-customer-orders_---id-customer-orders---">
                        <h3 class="card-header">
                            <i class="material-icons">shopping_basket</i> Commandes
                            <span class="badge badge-primary rounded">---nb-orders---</span>
                        </h3>                                                 
                          
                        <div class="card-body">                                                
                            <div class="row">
                                <div class="col">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Paiement</th>
                                                <th>État</th>
                                                <th>Produits</th>
                                                <th class="w-25">Total payé</th>
                                            </tr>
                                        </thead>
                                      
                                      <tbody>           
                                        ---table-customer-orders---
                                      </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                     </div>`,

    tableCustomerOrders: `<tr>
                             <td class="js-linkable-item cursor-pointer">---id-order---</td>
                             <td class="js-linkable-item cursor-pointer">---date-order---</td>
                             <td class="js-linkable-item cursor-pointer">---order-payment---</td>
                             <td class="js-linkable-item cursor-pointer">---order-status---</td>
                             <td class="js-linkable-item cursor-pointer">---nb-products---</td>
                             <td class="js-linkable-item cursor-pointer">---order-total-paid---</td>
                           </tr>`,

    customerCarts: `<div id="modal-customer-carts_---id-customer-carts---">
                        <h3 class="card-header">
                            <i class="material-icons">shopping_cart</i> Paniers
                            <span class="badge badge-primary rounded">---nb-carts---</span>
                        </h3>
                        
                      <div class="card-body">
                        <table class="table">
                            <thead>
                              <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Transporteur</th>
                                <th>Total</th>
                              </tr>
                            </thead>
                            
                            <tbody>
                            ---table-customer-carts---
                            </tbody>
                        </table>
                      </div>
                    </div>`,

    tableCustomerCarts: `<tr>
                             <td class="js-linkable-item cursor-pointer">---id-cart---</td>
                             <td class="js-linkable-item cursor-pointer">---date-cart---</td>
                             <td class="js-linkable-item cursor-pointer">---transporter---</td>
                             <td class="js-linkable-item cursor-pointer">---price---</td>
                         </tr>`,

    customerAddresses: `<div id="modal-customer-addresses_---id-customer-addresses---">
                            <h3 class="card-header">
                                <i class="material-icons">location_on</i> Adresses
                            
                                <a href="---add-address---" class="tooltip-link float-right" target="_blank" 
                                data-toggle="pstooltip" data-placement="top" data-original-title="Ajouter">
                                    <i class="material-icons">add_circle</i>
                                </a>
                            </h3>
                        
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                      <tr>
                                        <th>Société</th>
                                        <th>Nom</th>
                                        <th>Adresse</th>
                                        <th>Pays</th>
                                        <th>Numéro(s) de téléphone</th>
                                      </tr>
                                    </thead>
                                    
                                    <tbody>
                                        ---table-customer-addresses---
                                    </tbody>
                                </table>
                            </div>
                        </div>`,

    tableCustomerAddresses: `<tr>
                               <td class="js-linkable-item cursor-pointer">---company---</td>
                               <td class="js-linkable-item cursor-pointer">---firstname--- ---lastname---</td>
                               <td class="js-linkable-item cursor-pointer">---address--- ---further-address--- ---postcode--- ---city--- </td>
                               <td class="js-linkable-item cursor-pointer">---country---</td>
                               <td class="js-linkable-item cursor-pointer">---phone-number---</td>
                             </tr>`,

    tableCart: `<tr>
                    <td class="cart-id text-left">---cartId---</td>
                    <td class="cart-date text-left">---cartDate---</td>
                    <td class="cart-total text-left">---totalCart---</td>
                    <td class="cart-actions d-flex">
                        <button class="btn btn-light d-flex" data-toggle="modal" data-target="#showCartModal_---id-cart-modal---">
                            <span uk-icon="icon: search" class="mr-1 search-icon"></span> Details
                        </button>
                        <a href="---link-show-customer-cart-use---" data-idcart="---id---" class="customer-cart-to-use btn btn-light d-flex ml-3">
                            <span uk-icon="icon: arrow-right" class="mr-1"></span> Utiliser
                        </a>
                    </td>         
                 </tr>`,

    modalCartInfos: `<div class="modal fade" id="showCartModal_---id-cart-modal---" tabindex="-1" role="dialog" aria-labelledby="showCustomerModalTitle" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              
                              <div class="modal-body">
                                <div class="row">
                                   <div class="col">
                                    <div class="card">
                                      <h2>INFORMATIONS CLIENT</h2>
                                      <h3 class="card-header"> <i class="material-icons">person</i>
                                      ---firstname--- ---lastname--- [---id-customer---]
                                      </h3>
                                      <div class="card-body">
                                        <h2>CONTENU DU PANIER [---id-cart---]</h2>
        
                                        <table class="table">
                                             <thead>
                                                <tr>
                                                <th class="text-left">Produits</th>
                                                <th class="text-left">Prix Unitaire</th>
                                                <th class="text-left">Quantité</th>
                                                <th class="text-left">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            
                                                ---cart-data---
                                                
                                                <th colspan="3" class="text-left">Total produits</th>
                                                <th class="text-left">---totalCart---</th>
                                                </tr>
                                        </table>
        
                                      </div>
                                    </div>
                                   </div>
                                </div>
                            
                              </div>
                            </div>
                          </div>
                     </div>`,

    cartData: `<tr>
                    <td class="text-left">---productName---</td>
                    <td class="text-left">---productPrice---</td>
                    <td class="text-left">---productQuantity---</td>
                    <td class="text-left">---totalProduct---</td>
                <tr>`,

    tableOrder: `<tr>
            <td class="order-id text-left">---orderId---</td>
            <td class="order-date text-left">---orderDate---</td>
            <td class="order-total text-left">---totalOrder---</td>
            <td class="order-payment text-left">---payment---</td>
            <td class="order-status text-left">---orderStatus---</td>
            <td class="order-actions d-flex">
                <button class="btn btn-light d-flex" data-toggle="modal" data-target="#showOrderModal_---id-order-modal---">
                     <span uk-icon="icon: search" class="mr-1 search-icon"></span> Details
                </button>
                <a href="---link-show-customer-cart-use---" data-idcart="---id---" class="customer-cart-to-use btn btn-light d-flex ml-3">
                    <span uk-icon="icon: arrow-right" class="mr-1"></span> Utiliser
                </a>
            </td>
        </tr>`,

    modalCartOrderInfos: `<div class="modal fade" id="showOrderModal_---id-order-modal---" tabindex="-1" role="dialog" aria-labelledby="showCustomerModalTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                           <div class="col">
                            <div class="card">
                              <h2><i class="material-icons">person</i> INFORMATIONS CLIENT</h2>
                              <div class="card-header">
                              <h3>
                              ---firstname--- ---lastname--- [---id-customer---]
                              </h3> 
                              <h4>Adresse : </h4>
                              <p>
                              ---address1--- ---address2--- ---postcode--- ---city---
                              </p> 
                              </div>
                              <div class="card-body">
                                <h2><i class="material-icons">credit_card</i> COMMANDE [---id-order---] référence : ---reference---</h2>
                                <p>Etat de la commande : ---orderStatus---</p>
                                <h2><i class="material-icons">shopping_cart</i> CONTENU DU PANIER [---id-cart---]</h2>
                                <table class="table">
                                     <thead>
                                        <tr>
                                        <th class="text-left">Produits</th>
                                        <th class="text-left">Prix Unitaire</th>
                                        <th class="text-left">Quantité</th>
                                        <th class="text-left">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    
                                        ---order-cart-data---
                                        
                                        <tr>
                                        <td colspan="3" class="text-left">Total produits</td>
                                        <td class="text-left">---totalProducts---</td>
                                        </tr>
                                        <tr>
                                        <td colspan="3" class="text-left">Livraison</td>
                                        <td class="text-left">---totalShipping---</td>
                                        </tr>
                                        <tr>
                                        <th colspan="3" class="text-left">Total payé</th>
                                        <th class="text-left">---totalPaid---</th>
                                        </tr>
                                </table>
                              </div>
                            </div>
                           </div>
                        </div>
                    
                      </div>
                    </div>
                  </div>
                </div>`,

    tableQuotation: `<tr>
            <td class="quotation-id text-left">---quotationId---</td>
            <td class="quotation-date text-left">---quotationDate---</td>
            <td class="quotation-total text-left">---totalQuotation---</td>
            <td class="quotation-actions d-flex">
                <button class="btn btn-light d-flex" data-toggle="modal" data-target="#showQuotationModal_---id-quotation-modal---">
                    <span uk-icon="icon: search" class="mr-1 search-icon"></span> Details
                </button>
                <a href="---link-show-customer-cart-use---" data-idcart="---id---" class="customer-cart-to-use btn btn-light d-flex ml-3">
                    <span uk-icon="icon: arrow-right" class="mr-1"></span> Utiliser
                </a>
            </td>
        </tr>`,

    modalCartQuotationInfos: `<div class="modal fade" id="showQuotationModal_---id-quotation-modal---" tabindex="-1" role="dialog" aria-labelledby="showCustomerModalTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                           <div class="col">
                            <div class="card">
                              <h2><i class="material-icons">person</i> INFORMATIONS CLIENT</h2>
                              <div class="card-header">
                                  <h3>
                                  ---firstname--- ---lastname--- [---id-customer---]
                                  </h3>
                              </div>
                              <div class="card-body">
                                <h2><i class="material-icons">content_paste</i> DEVIS [---id-quotation---] référence : ---reference---</h2>
                                <h2><i class="material-icons">shopping_cart</i> CONTENU DU PANIER [---id-cart---]</h2>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Produits</th>
                                            <th class="text-left">Prix Unitaire</th>
                                            <th class="text-left">Quantité</th>
                                            <th class="text-left">Total</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                    
                                        ---quotation-cart-data---
                                        
                                        <tr>
                                        <td colspan="3" class="text-left">Total produits</td>
                                        <td class="text-left">---totalQuotation---</td>
                                        </tr>   
                                    </tbody>                                     
                                </table>
                              </div>
                            </div>
                           </div>
                        </div>                    
                      </div>
                    </div>
                  </div>
                </div>`,

    quotationCartProducts: `<tr>
                                <td class="text-left"><img src="---picture---"></td>
                                <td class="text-left">---productName---</td>
                                <td class="text-left">---productPrice---</td>
                                <td class="text-left">---productQuantity---</td>
                                <td class="text-left">---totalProduct---</td>
                            </tr>`,

    quotationCart: `<tr>
                        <th colspan="3" class="text-left">Total produits</th>
                        <th class="text-left">---totalCart---</th>
                    </tr>`,

    productQuantity: `---quantityInStock---`,

};
