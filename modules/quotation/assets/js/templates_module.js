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
                            
                                <a href="---edit---" class="tooltip-link float-right" data-toggle="pstooltip" title="" data-placement="top" data-original-title="Modifier">
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
                        
                        </div>
                    </div>`
};