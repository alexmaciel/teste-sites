import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';


import { 
  TechnologyService,
  TechnologyItemService,
} from '../../../../core';

@Component({
  selector: 'app-items',
  templateUrl: './items.component.html',
})
export class ItemsComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    // Services
    public technology: TechnologyService,
    public items: TechnologyItemService,
  ) {}   

  ngOnInit(): void {
    const sb = this.items.getItems().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  } 
  
  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  } 
  
}
