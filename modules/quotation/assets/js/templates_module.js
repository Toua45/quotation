export const TemplateModule = {
    card: `<div class="card" style="width: 18rem;">
              <div class="card-body">
                <h5 class="card-title">---lastname---</h5>
                <h6 class="card-subtitle mb-2 text-muted">---firstname---</h6>
                <p class="card-text">---text---</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#showCustomerModal">Details</button>   
                ---modal-customer-infos---         
              </div>
            </div>`,
    modalCustomerInfos: `<div class="modal fade" id="showCustomerModal" tabindex="-1" role="dialog" aria-labelledby="showCustomerModalTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                    
                    ---personal-datas---
                    
                      </div>
                    </div>
                  </div>
                </div>`,
    personalData: `<div class="row">
                        <div class="col">
                            <div class="card">
                              <h3 class="card-header"> <i class="material-icons">person</i>
                                ---firstname--- ---lastname--- [---id-customer---] -
                                <a href="---customer-link-email---">---customer-email---</a>
                            
                                <a href="/adm/index.php/sell/customers/2/edit?back=http%3A%2F%2Flocalhost%3A8000%2Fadm%2Findex.php%2Fsell%2Fcustomers%2F2%2Fview%3F_token%3DF_TMuQ-Rs-YMNx0dKQYHY_sFoe9xHK9_kDvr5uNV7qQ%26liteDisplaying%3D1&amp;_token=F_TMuQ-Rs-YMNx0dKQYHY_sFoe9xHK9_kDvr5uNV7qQ" class="tooltip-link float-right" data-toggle="pstooltip" title="" data-placement="top" data-original-title="Modifier">
                                  <i class="material-icons">edit</i>
                                </a>
                              </h3>
                              <div class="card-body">
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Titre de civilité
                                  </div>
                                  <div class="col-8">
                                    M
                                  </div>
                                </div>
                            
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Âge
                                  </div>
                                  <div class="col-8">
                                    50 ans (date de naissance : 15/01/1970)
                                  </div>
                                </div>
                            
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Date d'inscription
                                  </div>
                                  <div class="col-8">
                                    13/02/2020 10:09:27
                                  </div>
                                </div>
                            
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Dernière visite
                                  </div>
                                  <div class="col-8">
                                    13/02/2020 10:09:32
                                  </div>
                                </div>
                            
                                
                                
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Langue
                                  </div>
                                  <div class="col-8">
                                    Français (French)
                                  </div>
                                </div>
                            
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Inscriptions
                                  </div>
                                  <div class="col-8">
                                            
                                    <span class="badge badge-success rounded pt-0 pb-0">
                                      <i class="material-icons">check</i>
                                      Lettre d'informations
                                    </span>
                            
                                    <span class="badge badge-success rounded pt-0 pb-0">
                                      <i class="material-icons">check</i>
                                      Offres partenaires
                                    </span>
                                  </div>
                                </div>
                            
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    Dernière mise à Jour
                                  </div>
                                  <div class="col-8">
                                    13/02/2020 10:09:27
                                  </div>
                                </div>
                            
                                <div class="row mb-1">
                                  <div class="col-4 text-right">
                                    État
                                  </div>
                                  <div class="col-8">
                                    
                                    <span class="badge badge-success rounded pt-0 pb-0">
                                                  <i class="material-icons">check</i>
                                        Activé
                                              </span>
                                  </div>
                                </div>
                            
                                  </div>
                            </div>
                        
                        </div>
                    </div>`
};