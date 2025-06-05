import { TestBed } from '@angular/core/testing';

import { EntradaSalidaGruaService } from './entrada-salida-grua.service';

describe('EntradaSalidaGruaService', () => {
  let service: EntradaSalidaGruaService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EntradaSalidaGruaService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
