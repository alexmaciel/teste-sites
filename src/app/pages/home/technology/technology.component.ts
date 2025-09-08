import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

import { 
  TechnologyService,
  TechnologyItemService
} from '../../../core';

@Component({
  selector: 'app-technology',
  templateUrl: './technology.component.html'
})
export class TechnologyComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    // Services
    public technology: TechnologyService,
    public items: TechnologyItemService
  ) {}   

  ngOnInit(): void {
    this.loadTechnology();
    this.loadTechnologydItems();
  }  
    
  loadTechnology() {
    const sb = this.technology.getTechnology().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  }  
  
  loadTechnologydItems() {
    const sb = this.items.getItems().pipe(
    ).subscribe();
    this.subscriptions.push(sb);       
  }   
  
  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }  
}
