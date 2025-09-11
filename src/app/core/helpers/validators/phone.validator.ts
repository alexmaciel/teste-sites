import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';
import libphonenumber, { PhoneNumberFormat } from 'google-libphonenumber';


export class PhoneValidator {
  // Inspired on: https://github.com/yuyang041060120/ng2-validation/blob/master/src/equal-to/validator.ts
  static validCountryPhone(countryControl: AbstractControl): ValidatorFn {
    let subscribed = false;
    const phoneUtil = libphonenumber.PhoneNumberUtil.getInstance();

    return (phoneControl: AbstractControl): ValidationErrors | null => {
      if (!subscribed) {
        subscribed = true;
        countryControl.valueChanges.subscribe(() => {
          phoneControl.updateValueAndValidity();
        });
      }

      if (!phoneControl.value) {
        return null; // vazio ainda não valida
      }

      try {
        const region = countryControl.value; // se for só o ISO
        const number = phoneUtil.parseAndKeepRawInput(phoneControl.value, region);
        const isValid = phoneUtil.isValidNumberForRegion(number, region);

        return isValid ? null : { invalidPhone: true };
      } catch (e) {
        return { invalidPhone: true };
      }
    };
  };  
  

  static globalPhoneValidator = (): ValidatorFn => {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) return null;

      try {
        const phoneUtil = libphonenumber.PhoneNumberUtil.getInstance();
        // detecta o país automaticamente pelo prefixo
        const number = phoneUtil.parse(control.value);

        if (!phoneUtil.isValidNumber(number)) {
          return { invalidPhone: true };
        }

        const isValid = phoneUtil.isValidNumber(number);
        control.setValue(phoneUtil.format(number, PhoneNumberFormat.E164), { emitEvent: false });

        if (!isValid) {
          return { invalidPhone: true };
        }
        
        return null;
      } catch (e) {
        return { invalidPhone: true };
      }
    };
  }; 
}
