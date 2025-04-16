# Guía de Implementación de Habilidades (Skills) - Frontend

## Descripción General
Esta guía detalla la implementación del sistema de gestión de habilidades para usuarios en el frontend de la aplicación OiDiVi Helper. El sistema permite a los usuarios seleccionar y gestionar sus habilidades profesionales, siendo un requisito obligatorio para acceder a las funcionalidades principales de la plataforma.

## Tabla de Contenidos
1. [Requisitos Previos](#requisitos-previos)
2. [Estructura de Archivos](#estructura-de-archivos)
3. [Tipos de Datos](#tipos-de-datos)
4. [Servicios de API](#servicios-de-api)
5. [Hook Personalizado](#hook-personalizado)
6. [Componentes](#componentes)
7. [Integración con el Sistema de Rutas](#integración-con-el-sistema-de-rutas)
8. [Manejo de Estados](#manejo-de-estados)

## Requisitos Previos

- TypeScript
- Tailwind CSS
- shadcn/ui
- Axios para peticiones HTTP
- React Query (opcional para caché y manejo de estado)

## Estructura de Archivos

```plaintext
src/
├── lib/
│   ├── api/
│   │   └── user/
│   │       └── skills.ts       # Servicios de API para habilidades
│   └── types/
│       └── user/
│           └── skill.ts        # Tipos de datos
├── hooks/
│   └── user/
│       └── useUserSkills.ts    # Hook personalizado
├── components/
│   └── user/
│       └── skills/
│           └── SkillSelector.tsx # Componente principal
└── app/
    └── (user)/
        └── profile/
            └── skills/
                └── page.tsx     # Página de gestión de habilidades
```

## Tipos de Datos

```typescript
// src/lib/types/user/skill.ts

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
}

export interface Skill {
  id: number;
  name: string;
  description?: string;
  is_active: boolean;
  sort_order?: number;
  categories: Category[];
}

export interface UserSkill extends Skill {
  experience_level: number;
  last_updated_at: string;
}
```

## Servicios de API

```typescript
// src/lib/api/user/skills.ts

import { Skill, UserSkill } from '@/lib/types/user/skill';
import { apiClient } from '../index';

export const skillsApi = {
  getAvailable: async (): Promise<Skill[]> => {
    const response = await apiClient.get('/v1/user/skills/available');
    return response.data.data;
  },

  getUserSkills: async (): Promise<UserSkill[]> => {
    const response = await apiClient.get('/v1/user/skills');
    return response.data.data;
  },

  addUserSkills: async (skillIds: number[]): Promise<UserSkill[]> => {
    const response = await apiClient.post('/v1/user/skills', {
      skill_ids: skillIds
    });
    return response.data.data;
  },

  removeUserSkill: async (skillId: number): Promise<void> => {
    await apiClient.delete(`/v1/user/skills/${skillId}`);
  }
};
```

## Hook Personalizado

```typescript
// src/hooks/user/useUserSkills.ts

import { useState, useEffect } from 'react';
import { skillsApi } from '@/lib/api/user/skills';
import { useToast } from '@/components/ui/use-toast';

export const useUserSkills = () => {
  // ... Implementación del hook como se mostró anteriormente
};
```

## Componentes

### SkillSelector

```typescript
// src/components/user/skills/SkillSelector.tsx

import { useState } from 'react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface SkillSelectorProps {
  availableSkills: Skill[];
  selectedSkills: number[];
  onSkillsSelected: (skillIds: number[]) => Promise<void>;
  loading?: boolean;
}

export const SkillSelector = ({ ... }) => {
  // ... Implementación del componente como se mostró anteriormente
};
```

### Página de Habilidades

```typescript
// src/app/(user)/profile/skills/page.tsx

'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useUserSkills } from '@/hooks/user/useUserSkills';
import { SkillSelector } from '@/components/user/skills/SkillSelector';

export default function SkillsPage() {
  // ... Implementación de la página como se mostró anteriormente
};
```

## Integración con el Sistema de Rutas

### Middleware de Verificación

```typescript
// src/middleware.ts

import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  // ... Implementación del middleware como se mostró anteriormente
}

export const config = {
  matcher: [
    '/dashboard/:path*',
    '/service-requests/:path*',
    '/chat/:path*',
    '/profile/:path*',
  ],
};
```

## Manejo de Estados

### Estados de Usuario
- `needsSkillSetup`: Boolean que indica si el usuario necesita configurar sus habilidades
- `loading`: Estado de carga durante las operaciones
- `error`: Estado de error para manejo de excepciones

### Estados de Habilidades
- `availableSkills`: Lista de habilidades disponibles
- `userSkills`: Lista de habilidades del usuario
- `selectedSkills`: Habilidades seleccionadas temporalmente

## Implementación Paso a Paso

1. **Configuración Inicial**
   ```bash
   # Crear estructura de directorios
   mkdir -p src/lib/api/user
   mkdir -p src/lib/types/user
   mkdir -p src/hooks/user
   mkdir -p src/components/user/skills
   ```

2. **Instalación de Dependencias**
   ```bash
   # Asegurarse de tener todas las dependencias necesarias
   npm install @radix-ui/react-icons
   ```

3. **Copiar Archivos**
   - Copiar los tipos de datos
   - Implementar servicios de API
   - Crear hook personalizado
   - Implementar componentes

4. **Configuración de Rutas**
   - Implementar middleware
   - Crear página de habilidades

## Consideraciones de UX

1. **Feedback Visual**
   - Mostrar estado de carga
   - Indicadores de selección claros
   - Mensajes de error descriptivos
   - Confirmaciones de acciones exitosas

2. **Accesibilidad**
   - Usar roles ARIA apropiados
   - Asegurar navegación por teclado
   - Proporcionar textos alternativos

3. **Responsive Design**
   - Diseño adaptable a diferentes dispositivos
   - Grid responsivo para habilidades
   - Interacciones táctiles optimizadas

## Pruebas

1. **Pruebas Unitarias**
   - Componentes individuales
   - Hook personalizado
   - Servicios de API

2. **Pruebas de Integración**
   - Flujo completo de selección
   - Manejo de errores
   - Redirecciones

## Solución de Problemas

### Problemas Comunes y Soluciones

1. **Las habilidades no se cargan**
   - Verificar conexión con API
   - Comprobar token de autenticación
   - Revisar console.log para errores

2. **Selección no se guarda**
   - Validar payload de la petición
   - Verificar manejo de estado
   - Comprobar callbacks

3. **Redirección no funciona**
   - Revisar middleware
   - Verificar rutas protegidas
   - Comprobar estado de usuario

## Recursos Adicionales

- [Documentación de shadcn/ui](https://ui.shadcn.com/)
- [Next.js App Router](https://nextjs.org/docs/app)
- [TypeScript Documentation](https://www.typescriptlang.org/docs/)

## Soporte

Para preguntas o problemas específicos, contactar al equipo de desarrollo a través de:
- GitHub Issues
- Canal de Slack del proyecto
- Email de soporte técnico
