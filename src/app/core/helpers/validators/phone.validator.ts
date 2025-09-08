import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';
import libphonenumber, { PhoneNumberFormat } from 'google-libphonenumber';

//const phoneUtil = PhoneNumberUtil.getInstance();

export class PhoneValidator {
  // Inspired on: https://github.com/yuyang041060120/ng2-validation/blob/master/src/equal-to/validator.ts
  static validCountryPhone = (countryControl: AbstractControl): ValidatorFn => {
    let subscribe: boolean = false;

    return (phoneControl: AbstractControl): {[key: string]: boolean} => {
      if (!subscribe) {
        subscribe = true;
        countryControl.valueChanges.subscribe(() => {
          phoneControl.updateValueAndValidity();
        });
      }

      if(phoneControl.value !== ""){
        try{
            const phoneUtil = libphonenumber.PhoneNumberUtil.getInstance();
            // Parse number with country code and keep raw input.
            let phoneNumber = "" + phoneControl.value + "",
                region = countryControl.value.iso,
                number = phoneUtil.parse(phoneNumber, region),
                isValidNumber = phoneUtil.isValidNumber(number);

            if(isValidNumber){
                return { invalidPhone: true  };
            }
          }catch(e){
              // console.log(e);
              return {
                validCountryPhone: true
              };
          }

          return {
            validCountryPhone: true
        };
      }
      return {};
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
