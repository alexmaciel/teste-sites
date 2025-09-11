import { Component, Input, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { catchError, Observable, of, Subscription } from 'rxjs';

import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';

import {
  // Validators
  PhoneValidator,
} from '../../helpers/validators';

import { 
  TranslationService,
  SettingService,
  SendmailService,
  CountryService,
  SocialService,
  Countries,
} from '../../';

const LANG_TO_COUNTRY: Record<string, string> = {
  'pt': 'BR',
  'en': 'US',
  'es': 'UY'
};

@Component({
	selector: 'form-content',
	template: `
    <div class="modal-header">
        <div class="modal-title fs-4 fw-semibold" id="modal-title" ngbAutofocus>
            {{ 'header.requestTitle' | translate }} 
        </div>
        <button 
            type="button"
            class="btn btn-icon btn-outline btn-active-outline-light btn-active-icon-white btn-motion btn-active-motion-primary btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="close" aria-describedby="modal-title" 
            (click)="modal.close()">
            <span
                [inlineSVG]="'./assets/img/close.svg'" class="svg-icon svg-icon-2x svg-icon-dark">
            </span>      
            <span class="btn-motion-ripple"><span></span></span>    
        </button>     
    </div>
    <div class="modal-body">
        <form class="form form-label-right" [formGroup]="formGroup">
            <div class="form-group row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="form-floating">
                        <input 
                            type="text"
                            name="firstname"
                            id="firstname"
                            [placeholder]="'&nbsp;'"
                            class="form-control form-control-solid form-control-lg" 
                            [class.is-invalid]="isControlInvalid('firstname')"
                            [class.is-valid]="isControlValid('firstname')"                        
                            formControlName="firstname"
                            aria-label="first name"
                        />    
                        <label for="firstname" class="form-label required">{{ 'form.firstNameField' | translate }}</label>
                        <div class="valid-feedback" *ngIf="isControlValid('firstname')">{{ 'form.propertyfieldMsg' | translate }}</div>
                        <div class="invalid-feedback" *ngIf="controlHasError('required', 'firstname')">{{ 'form.firstNameReqVal' | translate }}</div>                             
                        <div class="invalid-feedback" *ngIf="controlHasError('minlength', 'firstname')">{{ 'form.propertyMinimumReq' | translate: {min: '3'} }}</div>                              
                        <div class="invalid-feedback" *ngIf="controlHasError('maxLength', 'firstname')">{{ 'form.propertyMaximumReq' | translate: {max: '20'} }}</div>                                                                      
                    </div>     
                </div>
                <div class="col-lg-6">
                    <div class="form-floating">
                        <input 
                            type="text"
                            name="lastname"
                            [placeholder]="'&nbsp;'"
                            class="form-control form-control-solid form-control-lg" 
                            [class.is-invalid]="isControlInvalid('lastname')"
                            [class.is-valid]="isControlValid('lastname')"     
                            formControlName="lastname"
                            aria-label="last name"
                        />
                        <label for="lastname" class="form-label required">{{ 'form.lastNameField' | translate }}</label>
                        <div class="valid-feedback" *ngIf="isControlValid('lastname')">{{ 'form.propertyfieldMsg' | translate }}</div>
                        <div class="invalid-feedback" *ngIf="controlHasError('required', 'lastname')">{{ 'form.lastNameReqVal' | translate }}</div>                             
                        <div class="invalid-feedback" *ngIf="controlHasError('minlength', 'lastname')">{{ 'form.propertyMinimumReq' | translate: {min: '3'} }}</div>                              
                        <div class="invalid-feedback" *ngIf="controlHasError('maxLength', 'lastname')">{{ 'form.propertyMaximumReq' | translate: {max: '20'} }}</div>                                           
                    </div>
                </div>                                                    
            </div>  
            <div class="form-group row mb-4">
                <div class="col-lg-12">   
                    <div class="input-group input-group-solid input-group-lg">                                    
                        <div class="form-floating">  
                            <input
                                class="form-control form-control-lg"
                                type="email"
                                name="email"
                                [placeholder]="'&nbsp;'"
                                [class.is-invalid]="isControlInvalid('email')"
                                [class.is-valid]="isControlValid('email')" 
                                formControlName="email" 
                                aria-label="email"
                            />
                            <label class="form-label required">{{ 'form.emailAddressField' | translate }}</label>
                        </div>  
                        <div class="input-group-text">
                            <span [inlineSVG]="'./assets/img/mail.svg'" class="svg-icon svg-icon-6 m-0"></span>                       
                        </div>                                      
                    </div>                            
                    <div class="valid-feedback" *ngIf="isControlValid('email')">{{ 'form.propertyfieldMsg' | translate }}</div>
                    <div class="invalid-feedback" *ngIf="controlHasError('required', 'email')">{{ 'form.emailReqVal' | translate }}</div>                             
                    <div class="invalid-feedback" *ngIf="controlHasError('pattern', 'email')">{{ 'form.emailPaternVal' | translate }}</div>                                 
                </div>
            </div>
            <div class="form-group row g-4" formGroupName="country_phone">
                <div class="col-lg-6">
                    <ng-select
                        [items]="countries.items$ | async"
                        [multiple]="false"
                        [virtualScroll]="false"
                        [clearable]="false"    
                        dropdownPosition="auto"   
                        labelForId="countries"
                        [placeholder]="'form.categoriesHelper' | translate"
                        bindLabel="name"
                        bindValue="iso"                                                 
                        formControlName="country"                           
                        class="form-select-solid form-select-lg">
                        <ng-template ng-option-tmp let-item="item" let-index="index" let-search="searchTerm">
                            <div class="d-flex align-items-center">														
                                <div class="d-flex justify-content-start flex-column">									
                                    <div class="text-dark fw-bold text-active-primary fs-6">
                                        {{ item.name }}
                                    </div>									
                                </div>
                            </div>                                                  
                        </ng-template>     
                    </ng-select>                                                        
                </div>                                                
                <div class="col-lg-6">    
                    <div>  
                        <div class="input-group input-group-solid input-group-lg">  
                            <div class="form-floating">  
                                <input
                                    type="tel"
                                    name="phonenumber"
                                    [placeholder]="'&nbsp;'"
                                    class="form-control form-control-lg"   
                                    [class.is-invalid]="isControlInvalid('country_phone')"
                                    [class.is-valid]="isControlValid('country_phone')"                                                                                                      
                                    formControlName="phonenumber"
                                    aria-label="phone"
                                />
                                <label class="form-label required">{{ 'form.phoneField' | translate }}</label>
                            </div>  
                            <div class="input-group-text">
                                <span [inlineSVG]="'./assets/img/phone.svg'" class="svg-icon svg-icon-6 m-0"></span>
                            </div>                                      
                        </div>                                                                          
                        <span class="form-text text-muted">{{ 'form.numberOnlyHelper' | translate }}</span>
                        <div class="valid-feedback" *ngIf="isControlValid('country_phone')">{{ 'form.propertyfieldMsg' | translate }}</div>
                        <div class="invalid-feedback" *ngIf="controlHasError('required', 'country_phone')">{{ 'form.phoneReqVal' | translate }}</div>   
                        <div class="invalid-feedback" *ngIf="controlHasError('pattern', 'country_phone')">{{ 'form.numberOnlyHelper' | translate }}</div>    
                    </div>                               
                </div> 
            </div>
            <div class="form-group row mb-4">
                <div class="col-lg-12">
                    <label class="form-label required" for="message">&nbsp;</label>   
                    <quill-editor
                        name="message"
                        formControlName="message"
                        placeholder="Escreve sua mensagem"
                        [class.is-invalid]="isControlInvalid('message')"
                        [class.is-valid]="isControlValid('message')"                                       
                        class="form-control form-control-solid w-100"
                        aria-label="message">
                        <div quill-editor-toolbar>                                      
                            <span class="ql-formats">
                                <button class="ql-bold" [title]="'Bold'"></button>
                                <button class="ql-italic" [title]="'Italic'"></button>
                                <button class="ql-underline" [title]="'Undeline'"></button>
                            </span>                            
                            <span class="ql-formats">
                                <button class="ql-clean" [title]="'Clean'"></button>
                            </span>
                        </div>                                
                    </quill-editor>                                                                    
                    <div class="valid-feedback" *ngIf="isControlValid('message')">{{ 'form.propertyfieldMsg' | translate }}</div>
                    <div class="invalid-feedback" *ngIf="controlHasError('required', 'message')">{{ 'form.messageReqVal' | translate }}</div>                             
                    <div class="invalid-feedback" *ngIf="controlHasError('minlength', 'message')">{{ 'form.propertyMinimumReq' | translate: {min: '3'} }}</div>                                                                                      
                </div>
            </div>   
        </form>
    </div>
    <div class="modal-footer" [formGroup]="formGroup">
      <div class="d-flex align-items-start justify-content-between w-100">
        <div class="form-check form-check-custom form-check-solid form-check-md w-md-400px">
            <input class="form-check-input" type="checkbox" formControlName="terms" id="politica" checked="checked" />
            <label class="form-check-label" for="politica">
                {{ 'form.policyField' | translate }} <a (click)="routeToPage('/tdu')" class="fw-semibold text-primary">{{ 'nav.privacyNav' | translate }}</a>
            </label>
        </div>                                     
        <div class="d-flex align-items-center py-3"> 
            <button type="button" class="btn btn-sm btn-link btn-text-muted fw-bold me-8" (click)="modal.dismiss()">
                {{ 'button.cancelBtn' | translate }}
            </button>            
            <button type="submit" (click)="onSubmit()" [disabled]="formGroup.invalid" class="btn btn-sm btn-primary btn-motion btn-active-primary btn-active-motion-secondary rounded-pill" data-magnet>
                <ng-container *ngIf="isLoading$ | async">
                    <span class="indicator-progress" [style.display]="'block'">
                    Sending...
                    <span
                        class="spinner-border spinner-border-sm align-middle m-2"
                    ></span>
                    </span>
                </ng-container>     
                <ng-container *ngIf="!(isLoading$ | async)">   
                    <span class="btn-icon">
                        <span [inlineSVG]="'./assets/img/plane.svg'" class="svg-icon svg-icon-dark svg-icon-4 me-1"></span>               
                    </span>      
                    <span class="btn-title">
                        <span title="enviar" class="indicator-label">{{ 'button.sendBtn' | translate }}</span>
                    </span> 
                    <span class="btn-motion-ripple"><span></span></span> 
                </ng-container>
            </button>               
        </div>  
      </div> 
    </div>  
	`,
  encapsulation: ViewEncapsulation.None,
})
export class FormComponent {

  isLoading$?: Observable<boolean>;

  selectedTerms = true;
  success = false;

  formGroup!: FormGroup;
  countryPhoneGroup!: FormGroup;

  countriesPhone: Countries[] = [];
  selectedCountry!: any;

  private unsubscribe: Subscription[] = [];
    
  constructor(
    private fb: FormBuilder,
    private router: Router,
    private translation: TranslationService,  
    private localize: LocalizeRouterService,        
    // Services
    public countries: CountryService,  
    public settings: SettingService,  
    public socials: SocialService,
    // Send Email
    private send: SendmailService,    
    public modal: NgbActiveModal,
    // seo
    //private readonly jsonLdService: JsonLdService,     
  ) { }

  ngOnInit(): void {
    this.loadCountries();
    this.loadForm();
  }  

  loadCountries() {
    const sb = this.countries.getCountries().subscribe();  
    this.unsubscribe.push(sb)      
  }

  loadForm() {
    //  We just use a few random countries, however, you can use the countries you need by just adding them to this list.
    // also you can use a library to get all the countries from the world.    
    const defaultIso = LANG_TO_COUNTRY[this.translation.getSelectedLanguage()]; // fallback    
    this.selectedCountry = this.countriesPhone.filter(c => c.iso == defaultIso)[0] ?? 'UY';

    const country = new FormControl(this.selectedCountry?.iso || 'UY', Validators.required);
    const phonenumber = new FormControl(this.selectedCountry?.code || '598', Validators.compose([
      Validators.required,
      PhoneValidator.validCountryPhone(country)
    ]));
    this.countryPhoneGroup = new FormGroup({
      country: country,
      phonenumber: phonenumber
    });  

    country.valueChanges.subscribe((iso) => {
      this.selectedCountry = this.countriesPhone.find(c => c.iso === iso);
      if (this.selectedCountry) {
        phonenumber.setValue(this.selectedCountry.code, { emitEvent: false });
      }
    }); 

    this.formGroup = this.fb.group({
      subject: ["", Validators.compose([
        Validators.required
      ])],
      firstname: ["", Validators.compose([
          Validators.required, 
          Validators.minLength(3), 
          Validators.maxLength(20)
      ])],
      lastname: ["", Validators.compose([
          Validators.required, 
          Validators.minLength(3), 
          Validators.maxLength(20)
      ])], 
      email: ["", Validators.compose([
        Validators.required, 
        Validators.pattern('^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$')
      ])],
      country_phone: this.countryPhoneGroup,
      message: ["", Validators.compose([
        Validators.required,
        Validators.minLength(3), 
      ])],      
      terms: [this.selectedTerms, Validators.pattern('true')],           
    })
  }  

  onSubmit(): void {
    this.formGroup.markAllAsTouched();
    if (!this.formGroup.valid) {
      return;
    }    

    const formData = new FormData();
    formData.append('subject', this.formGroup.get('subject')?.value);
    formData.append('firstname', this.formGroup.get('firstname')?.value);
    formData.append('lastname', this.formGroup.get('lastname')?.value);
    formData.append('email', this.formGroup.get('email')?.value);
    formData.append('phone', this.formGroup.get('country_phone')?.get('phonenumber')?.value);
    formData.append('message', this.formGroup.get('message')?.value);


    const sb = this.send.sendEmail(formData).pipe(
      catchError((errorMessage) => {
        console.error('UPDATE ERROR', errorMessage);
        return of(undefined);
      })
    ).subscribe((res) => {
      if(res.type == 'success') {
        this.success = true;
        this.success = res.message;
      } else {
        this.success = res.message;
      }
    });
    this.unsubscribe.push(sb);   
  }
    
  ngOnDestroy() {
    this.unsubscribe.forEach((sb) => sb.unsubscribe());
  }    

  routeToPage(path?: string) {
    const translatedPath = this.localize.translateRoute(`${path}`);

    this.router.navigate([translatedPath]).then(() => {
      // console.log(`After navigation I am on: ${translatedPath}`)
     }); 
  } 

  // helpers for View
  isControlValid(controlName: string): boolean {
    const control = this.formGroup.controls[controlName];
    return control.valid && (control.dirty || control.touched);
  }

  isControlInvalid(controlName: string): boolean {
    const control = this.formGroup.controls[controlName];
    return control.invalid && (control.dirty || control.touched);
  }

  controlHasError(validation: string, controlName: string): boolean {
    const control = this.formGroup.controls[controlName];
    return control.hasError(validation) && (control.dirty || control.touched);
  }

  isControlTouched(controlName: string): boolean {
    const control = this.formGroup.controls[controlName];
    return control.dirty || control.touched;
  }  
}


@Component({
  selector: 'app-request',
  templateUrl: './request.component.html',
  encapsulation: ViewEncapsulation.None,
})
export class RequestComponent {
  @Input() class: string | undefined | 'btn btn-lg btn-primary btn-active-primary btn-motion btn-active-motion-secondary';
  @Input() title: string | undefined | 'request demo';

  constructor(
    public modal: NgbModal,   
  ) { }

	open() {
    const modalRef = this.modal.open(FormComponent, {
      windowClass: 'modal-sticky modal-sticky-lg modal-sticky-bottom-end modal-dialog-scrollable',
      size: 'lg',
      backdrop: true,   
    });
    modalRef.result.then(
      () => { },
      () => { }
    );   
	}

}
