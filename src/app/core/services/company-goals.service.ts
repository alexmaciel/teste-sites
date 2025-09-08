import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';

import { Goals } from '../models';
import { TranslationService } from './translation.service';

@Injectable({
  providedIn: 'root'
})
export class CompanyGoalService implements OnDestroy  {

  // Public fields
  public _items$ = new BehaviorSubject<Goals[]>([]);
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


  getGoals(): Observable<Goals[]> { 
    return this.http.get<Goals[]>(`${this.API_URL}/companyGoals`).pipe(
      map((response: Goals[]) => {
        const result = response.filter(a => a.language === this.translation.currentLanguageValue);
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
