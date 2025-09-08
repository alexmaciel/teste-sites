import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';

import { Category } from '../models/category.model';
import { TranslationService } from './translation.service';

@Injectable({
  providedIn: 'root'
})
export class CategoryService implements OnDestroy {

  // Public fields
  public _items$ = new BehaviorSubject<Category[]>([]);
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
  constructor(http: HttpClient, private translation: TranslationService) {
    this.http = http;
  }

  // READ
  getCategories(): Observable<Category[]> { 
    return this.http.get<Category[]>(`${this.API_URL}/categories`).pipe(
      map((response: Category[]) => {
        const result = response.filter(a => a.language === this.translation.currentLanguageValue);
        if(result) {
          this._items$.next(result);
        }
        return result;
      }),
    );
  }  

  getPostCategoriesById(post_id?: number): Observable<Category[]> { 
    return this.http.get<Category[]>(`${this.API_URL}/category/${post_id}`).pipe(
      map((response: Category[]) => {
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
