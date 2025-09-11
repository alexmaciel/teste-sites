import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { NavigationEnd, Router } from '@angular/router';
import { catchError, filter, Observable, of, Subscription } from 'rxjs';


import {
  // Validators
  CountryPhone,
  PhoneValidator,
} from '../../core/helpers/validators';

import { 
  TranslationService,
  SettingService,
  SendmailService,
  CountryService,
  SocialService,
  Countries,
} from '../../core';

const LANG_TO_COUNTRY: Record<string, string> = {
  'pt': 'BR',
  'en': 'US',
  'es': 'UY'
};

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html'
})
export class ContactComponent implements OnInit, OnDestroy {

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
    private translation: TranslationService,
    private router: Router,
    // Services
    public countries: CountryService,  
    public settings: SettingService,  
    public socials: SocialService,
    // Send Email
    private send: SendmailService,
  ) { 
    if (typeof document !== 'undefined') {
      document.body.setAttribute('data-mv-app-header-color', 'color');
    }      
  }
  
  ngOnInit(): void {
    this.loadCountries();
    this.loadSocial();
 
    /*
    const sb = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        const newIso = LANG_TO_COUNTRY[this.translation.getSelectedLanguage()] ?? 'UY'; // fallback

        this.selectedCountry = this.countriesPhone.filter(c => c.iso === newIso)[0];
        console.log(this.selectedCountry)
      }
    });
    this.unsubscribe.push(sb);
    */
    this.loadForm();
  }

  loadSocial() {
    const sb = this.socials.getSocial().subscribe();
    this.unsubscribe.push(sb) 
  }

  loadCountries() {
    const defaultIso = LANG_TO_COUNTRY[this.translation.getSelectedLanguage()] ?? 'UY'; // fallback

    const sb = this.countries.getCountries().subscribe((res) => {
      this.countriesPhone = res;
      this.selectedCountry = this.countriesPhone.filter(c => c.iso == defaultIso)[0];
      //this.selectedCountry = this.countriesPhone[0];
    });  
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
