import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';

import { Company } from '../models/';
import { TranslationService } from './translation.service';

@Injectable({
  providedIn: 'root'
})
export class CompanyService implements OnDestroy  {

  // Public fields
  public _items$ = new BehaviorSubject<Company | undefined>(undefined);
  public _subscriptions: Subscription[] = [];

  // Getters
  get items$() {
    return this._items$.asObservable();
  }
  get subscriptions() {
    return this._subscriptions;
  }  

  protected http: HttpClient;
  // API URL has to be overrided
  API_URL = `${environment.apiUrl}/clients`;
  constructor(http: HttpClient, private translation: TranslationService) {
    this.http = http;
  }

  getCompany(): Observable<Company> { 
    return this.http.get<Company[]>(`${this.API_URL}/company`).pipe(
      map((response: Company[]) => {
        const result = response.filter(a => a.language === this.translation.currentLanguageValue)[0];
        if(result) {
          this._items$.next(result);
        }
        return result;
      }),
    );
  } 

  ngOnDestroy() {
    this.subscriptions.forEach(sb => sb.unsubscribe());
  }  
}
