import { NgModule } from '@angular/core';
import { CommonModule, NgOptimizedImage, IMAGE_LOADER, ImageLoaderConfig } from '@angular/common';


const customLoader = (config: ImageLoaderConfig) => {
  let url = `${config.src}`;
  let queryParams = [];
  if (config.width) {
    queryParams.push(`?w=${config.width}`);
  }

  return url + queryParams.join('&');
};

@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    NgOptimizedImage
  ],
  exports: [
    NgOptimizedImage
  ],
  // Configure the loader using the `IMAGE_LOADER` token.
  providers: [
    {
      provide: IMAGE_LOADER,
      useValue: customLoader
    },
  ],   
})
export class NgxOptimizedImageModule { }
