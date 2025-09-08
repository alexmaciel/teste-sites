import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';
import { Language } from '../models/language.model';

@Injectable({
  providedIn: 'root'
})
export class LanguageService implements OnDestroy {

  // Public fields
  public _items$ = new BehaviorSubject<Language[]>([]);
  // Private fields
  private _subscriptions: Subscription[] = [];

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
  constructor(http: HttpClient) {
    this.http = http;
  }

  // READ
  getLanguages(): Observable<Language[]> { 
    return this.http.get<Language[]>(`${this.API_URL}/languages`).pipe(
      map((response: Language[]) => {
        this._items$.next(response);
        return response;
      }),
    );
  }  
  
   
  ngOnDestroy() {
    this.subscriptions.forEach(sb => sb.unsubscribe());
  }  
}
